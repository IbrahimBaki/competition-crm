<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministratorInvariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolesSeeder']);
    }

    public function test_cannot_remove_admin_permissions_from_administrator_role(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::where('name', Role::ADMINISTRATOR)->first();
        $admin->roles()->attach($adminRole);

        $this->actingAs($admin);

        $response = $this->patchJson("/api/v1/roles/{$adminRole->uuid}", [
            'display_name' => ['ar' => 'مدير', 'en' => 'Admin'],
            'permission_keys' => [
                PermissionKey::ORG_BRANCHES_VIEW_ANY,
                PermissionKey::ORG_BRANCHES_MANAGE_ANY,
            ],
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertEquals('admin_role_locked', $response->json('error.code'));
    }

    public function test_cannot_delete_administrator_role(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::where('name', Role::ADMINISTRATOR)->first();
        $admin->roles()->attach($adminRole);

        $this->actingAs($admin);

        $response = $this->deleteJson("/api/v1/roles/{$adminRole->uuid}");

        $this->assertEquals(422, $response->status());
        $this->assertEquals('system_role_immutable', $response->json('error.code'));
    }
}
