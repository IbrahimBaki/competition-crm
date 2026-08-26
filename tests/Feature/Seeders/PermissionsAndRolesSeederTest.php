<?php

namespace Tests\Feature\Seeders;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PermissionsAndRolesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_role_has_all_permissions(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $adminRole = Role::where('name', Role::ADMINISTRATOR)->firstOrFail();
        $adminPermissions = $adminRole->permissions()->pluck('permission_key')->toArray();
        $allPermissions = PermissionKey::all();

        $this->assertEqualsCanonicalizing($allPermissions, $adminPermissions);
    }

    public function test_manager_role_has_correct_permissions(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $managerRole = Role::where('name', Role::MANAGER)->firstOrFail();
        $managerPermissions = $managerRole->permissions()->pluck('permission_key')->toArray();

        $expectedPermissions = [
            PermissionKey::ADMIN_ROLES_MANAGE,
            PermissionKey::ADMIN_USERS_MANAGE,
            PermissionKey::ADMIN_USERS_INVITE,
            PermissionKey::ADMIN_USERS_ACTIVATE,
            PermissionKey::ADMIN_USERS_DEACTIVATE,
            PermissionKey::ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY,
            PermissionKey::ADMIN_STRUCTURE_MANAGE,
            PermissionKey::ORG_BRANCHES_VIEW_ANY,
            PermissionKey::ORG_BRANCHES_MANAGE_ANY,
            PermissionKey::ORG_DEPARTMENTS_VIEW_ANY,
            PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY,
            PermissionKey::ORG_TEAMS_VIEW_ANY,
            PermissionKey::ORG_TEAMS_MANAGE_ANY,
            PermissionKey::TICKETS_VIEW_ANY,
        ];

        $this->assertEqualsCanonicalizing($expectedPermissions, $managerPermissions);
    }

    public function test_supervisor_role_has_correct_permissions(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $supervisorRole = Role::where('name', Role::SUPERVISOR)->firstOrFail();
        $supervisorPermissions = $supervisorRole->permissions()->pluck('permission_key')->toArray();

        $expectedPermissions = [
            PermissionKey::ORG_BRANCHES_VIEW_ANY,
            PermissionKey::ORG_DEPARTMENTS_VIEW_ANY,
            PermissionKey::ORG_TEAMS_VIEW_ANY,
            PermissionKey::TICKETS_VIEW_DEPARTMENT,
            PermissionKey::TICKETS_VIEW_TEAM,
        ];

        $this->assertEqualsCanonicalizing($expectedPermissions, $supervisorPermissions);
    }

    public function test_agent_role_has_correct_permissions(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $agentRole = Role::where('name', Role::AGENT)->firstOrFail();
        $agentPermissions = $agentRole->permissions()->pluck('permission_key')->toArray();

        $expectedPermissions = [
            PermissionKey::TICKETS_VIEW_TEAM,
            PermissionKey::TICKETS_VIEW_OWN,
        ];

        $this->assertEqualsCanonicalizing($expectedPermissions, $agentPermissions);
    }

    public function test_viewer_role_has_correct_permissions(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $viewerRole = Role::where('name', Role::VIEWER)->firstOrFail();
        $viewerPermissions = $viewerRole->permissions()->pluck('permission_key')->toArray();

        $expectedPermissions = [
            PermissionKey::TICKETS_VIEW_OWN,
        ];

        $this->assertEqualsCanonicalizing($expectedPermissions, $viewerPermissions);
    }

    public function test_all_system_roles_are_marked_as_system(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $systemRoles = [
            Role::ADMINISTRATOR,
            Role::MANAGER,
            Role::SUPERVISOR,
            Role::AGENT,
            Role::VIEWER,
        ];

        foreach ($systemRoles as $roleName) {
            $role = Role::where('name', $roleName)->firstOrFail();
            $this->assertTrue($role->is_system, "Role {$roleName} should be marked as system role");
        }
    }

    public function test_custom_non_system_role_permissions_are_preserved_on_reseed(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $customRole = Role::create([
            'name' => 'custom_role',
            'display_name' => ['ar' => 'دور مخصص', 'en' => 'Custom Role'],
            'is_system' => false,
        ]);

        $customRole->permissions()->create(['permission_key' => PermissionKey::TICKETS_VIEW_OWN]);

        Artisan::call('db:seed', ['--class' => 'Database\Seeders\PermissionsAndRolesSeeder']);

        $customRole->refresh();
        $permissions = $customRole->permissions()->pluck('permission_key')->toArray();

        $this->assertEqualsCanonicalizing([PermissionKey::TICKETS_VIEW_OWN], $permissions);
    }

    public function test_all_roles_have_uuid(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $roles = Role::all();

        foreach ($roles as $role) {
            $this->assertNotNull($role->uuid, "Role {$role->name} should have a UUID");
            $this->assertEquals(36, strlen($role->uuid), "Role {$role->name} UUID should be 36 characters");
        }
    }
}
