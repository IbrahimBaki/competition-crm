<?php

namespace Tests\Support;

use App\Domains\Security\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Support\Str;

/**
 * Test-only permission granting.
 *
 * Permissions reach a user exclusively through roles (see User::permissionKeys),
 * and that is deliberate — CLAUDE.md requires authorisation to be driven by
 * permission keys resolved from roles, never ad-hoc grants. So this helper does
 * not add a `grantPermission()` method to the production User model; it creates
 * a throwaway non-system role per user and attaches the keys to that.
 *
 * Non-system roles are also skipped by PermissionsAndRolesSeeder::reconcilePermissions,
 * so a re-seed inside a test will not strip the grants made here.
 */
trait InteractsWithPermissions
{
    /**
     * Authenticate as a user holding the Administrator role.
     *
     * Gate::before short-circuits every permission key for Administrators, so
     * this is the right actor for tests that exercise API conventions
     * (pagination, envelope shape, UUID exposure) rather than authorisation.
     */
    protected function actingAsAdministrator(): User
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $user = User::factory()->create();
        $admin = Role::where('name', Role::ADMINISTRATOR)->firstOrFail();
        $user->roles()->attach($admin->id);

        $this->actingAs($user);

        return $user;
    }

    /**
     * Grant one or more permission keys to a user via a per-user scratch role.
     */
    protected function grantPermission(User $user, string ...$permissionKeys): User
    {
        $role = $this->scratchRoleFor($user);

        $existing = $role->permissions()->pluck('permission_key')->all();

        foreach (array_diff($permissionKeys, $existing) as $key) {
            $role->permissions()->create(['permission_key' => $key]);
        }

        // permissionKeys() memoises per instance; drop the cache so the next
        // authorisation check sees what we just granted.
        return $this->forgetPermissionCache($user);
    }

    /**
     * Revoke permission keys previously granted through the scratch role.
     */
    protected function revokePermission(User $user, string ...$permissionKeys): User
    {
        $this->scratchRoleFor($user)
            ->permissions()
            ->whereIn('permission_key', $permissionKeys)
            ->delete();

        return $this->forgetPermissionCache($user);
    }

    private function scratchRoleFor(User $user): Role
    {
        $name = 'test_scratch_'.$user->uuid;

        $role = Role::firstOrCreate(
            ['name' => $name],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => ['ar' => 'دور اختبار', 'en' => 'Test scratch role'],
                'is_system' => false,
            ]
        );

        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }

        return $role;
    }

    private function forgetPermissionCache(User $user): User
    {
        // The cache lives on the model instance, and relations were loaded
        // before the grant, so refresh both.
        $user->unsetRelation('roles');

        (function () {
            unset($this->_permissionKeysCache);
        })->call($user);

        return $user;
    }
}
