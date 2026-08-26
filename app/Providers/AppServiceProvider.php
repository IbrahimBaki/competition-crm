<?php

namespace App\Providers;

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
use App\Support\Http\RequestId;
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

        $this->app->singleton(WorkingTimeService::class);
        $this->app->singleton(RequestId::class);
        $this->app->singleton(LocalizationSettings::class);
        $this->app->singleton(LocaleResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

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
