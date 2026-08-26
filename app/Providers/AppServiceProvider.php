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
use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Policies\RolePolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WorkingTimeService::class);

        $this->app->bind(
            DepartmentUsageChecker::class,
            NullDepartmentUsageChecker::class
        );

        $this->app->bind(
            BranchUsageChecker::class,
            DefaultBranchUsageChecker::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

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
    }
}
