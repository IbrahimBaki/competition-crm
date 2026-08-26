<?php

namespace App\Providers;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Policies\CustomerContactPolicy;
use App\Domains\Customers\Policies\CustomerNotePolicy;
use App\Domains\Customers\Policies\CustomerPolicy;
use App\Domains\Customers\Services\ArabicTextNormaliser;
use App\Domains\Customers\Services\Retention\CustomerNotePurgeHandler;
use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Customers\Services\Timeline\Sources\CustomerEventSource;
use App\Domains\Customers\Services\Timeline\Sources\NoteTimelineSource;
use App\Domains\Customers\Services\Timeline\TimelineRegistry;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Models\Team;
use App\Domains\Organisation\Policies\BranchPolicy;
use App\Domains\Organisation\Policies\DepartmentPolicy;
use App\Domains\Organisation\Policies\TeamPolicy;
use App\Domains\Organisation\Services\BranchUsageChecker;
use App\Domains\Organisation\Services\DefaultBranchUsageChecker;
use App\Domains\Organisation\Services\DepartmentUsageChecker;
use App\Domains\Organisation\Services\NullDepartmentUsageChecker;
use App\Domains\Organisation\Services\WorkingTimeService;
use App\Domains\Security\Models\AuditLog;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Policies\AuditLogPolicy;
use App\Domains\Security\Policies\RolePolicy;
use App\Domains\Security\Policies\UserPolicy;
use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\Policies\AttachmentPolicy;
use App\Support\Attachments\Scanning\MalwareScanner;
use App\Support\Attachments\Scanning\NullMalwareScanner;
use App\Support\Http\BotProtection\BotProtectionGuard;
use App\Support\Http\BotProtection\NullBotProtectionGuard;
use App\Support\Http\RequestId;
use App\Support\I18n\LocaleResolver;
use App\Support\I18n\LocalizationSettings;
use App\Support\Retention\Handlers\AttachmentPurgeHandler;
use App\Support\Retention\Handlers\AuditPurgeHandler;
use App\Support\Retention\Handlers\NullPurgeHandler;
use App\Support\Retention\RetentionRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            DepartmentUsageChecker::class,
            NullDepartmentUsageChecker::class
        );

        $this->app->bind(
            BranchUsageChecker::class,
            DefaultBranchUsageChecker::class
        );

        $driver = config('security.scanning.driver');
        $this->app->bind(
            MalwareScanner::class,
            $driver === 'null' ? NullMalwareScanner::class : NullMalwareScanner::class
        );

        $botDriver = config('security.public_endpoints.bot_protection.driver');
        $this->app->bind(
            BotProtectionGuard::class,
            $botDriver === 'null' ? NullBotProtectionGuard::class : NullBotProtectionGuard::class
        );

        $this->app->singleton(WorkingTimeService::class);
        $this->app->singleton(RequestId::class);
        $this->app->singleton(LocalizationSettings::class);
        $this->app->singleton(LocaleResolver::class);

        $this->app->bind(TextNormaliser::class, ArabicTextNormaliser::class);

        $this->app->singleton(TimelineRegistry::class, function ($app) {
            $registry = new TimelineRegistry;
            $registry->register($app->make(CustomerEventSource::class));
            $registry->register($app->make(NoteTimelineSource::class));
            // Ticket and channel-message sources register here once BE-Ticketing lands.

            return $registry;
        });

        $this->app->singleton(RetentionRegistry::class, function ($app) {
            $registry = new RetentionRegistry;
            $registry->register($app->make(AttachmentPurgeHandler::class));
            $registry->register($app->make(AuditPurgeHandler::class));
            $registry->register($app->make(CustomerNotePurgeHandler::class));
            $registry->register(new NullPurgeHandler('tickets'));
            $registry->register(new NullPurgeHandler('messages'));
            $registry->register(new NullPurgeHandler('logs'));

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomerContact::class, CustomerContactPolicy::class);
        Gate::policy(CustomerNote::class, CustomerNotePolicy::class);

        foreach (PermissionKey::all() as $key) {
            Gate::define($key, fn (User $user) => in_array($key, $user->permissionKeys(), true));
        }

        Gate::before(function (User $user, string $ability) {
            if (in_array($ability, PermissionKey::all(), true)
                && $user->roles()->where('name', Role::ADMINISTRATOR)->exists()) {
                return true;
            }

            return null;
        });

        RateLimiter::for('api', function ($request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(120)->by((string) $key);
        });

        RateLimiter::for('api-writes', function ($request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)->by((string) $key);
        });

        RateLimiter::for('public', function ($request) {
            $throttleConfig = config('security.public_endpoints.throttle');
            [$limit, $window] = explode(',', $throttleConfig);

            return Limit::perMinute((int) $limit)->by($request->ip());
        });

        RateLimiter::for('uploads', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        Queue::createPayloadUsing(function () {
            $currentId = RequestId::current();

            return ! empty($currentId) ? ['request_id' => $currentId] : [];
        });

        Queue::before(function ($event) {
            $requestId = $event->job->payload()['request_id'] ?? null;
            if ($requestId) {
                RequestId::set($requestId);
            }
        });

        Queue::after(function ($event) {
            RequestId::set('');
        });

        Queue::failing(function ($event) {
            RequestId::set('');
        });
    }
}
