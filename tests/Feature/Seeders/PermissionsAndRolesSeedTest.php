<?php

namespace Tests\Feature\Seeders;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsAndRolesSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_audit_view_permission_is_seeded(): void
    {
        $this->seed();

        $administrator = Role::where('name', 'administrator')->first();
        $this->assertNotNull($administrator);

        $permissions = $administrator->permissions->pluck('permission_key')->toArray();
        $this->assertContains(PermissionKey::ADMIN_AUDIT_VIEW, $permissions);
    }

    public function test_admin_audit_view_permission_exists_in_permission_key(): void
    {
        $allPermissions = PermissionKey::all();
        $this->assertContains(PermissionKey::ADMIN_AUDIT_VIEW, $allPermissions);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed();
        $initialAdminPerms = Role::where('name', 'administrator')
            ->first()
            ->permissions()
            ->count();

        $this->seed();
        $finalAdminPerms = Role::where('name', 'administrator')
            ->first()
            ->permissions()
            ->count();

        $this->assertEquals($initialAdminPerms, $finalAdminPerms);
    }
}
