<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_no_roles_gets_403_on_branches_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/branches');

        $this->assertEquals(403, $response->status());
    }

    public function test_user_with_view_permission_can_list_branches(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'viewer',
            'display_name' => ['ar' => 'عارض', 'en' => 'Viewer'],
        ]);
        $role->permissions()->create(['permission_key' => PermissionKey::ORG_BRANCHES_VIEW_ANY]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/branches');

        $this->assertEquals(200, $response->status());
    }

    public function test_user_with_view_only_cannot_create_branches(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'viewer',
            'display_name' => ['ar' => 'عارض', 'en' => 'Viewer'],
        ]);
        $role->permissions()->create(['permission_key' => PermissionKey::ORG_BRANCHES_VIEW_ANY]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $response = $this->postJson('/api/v1/branches', [
            'name' => 'Test Branch',
            'display_name' => ['ar' => 'فرع تجريبي', 'en' => 'Test Branch'],
            'timezone' => 'UTC',
        ]);

        $this->assertEquals(403, $response->status());
    }

    public function test_user_with_manage_permission_can_create_branches(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'manager',
            'display_name' => ['ar' => 'مدير', 'en' => 'Manager'],
        ]);
        $role->permissions()->create(['permission_key' => PermissionKey::ORG_BRANCHES_MANAGE_ANY]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $response = $this->postJson('/api/v1/branches', [
            'name' => 'test-branch',
            'display_name' => ['ar' => 'فرع تجريبي', 'en' => 'Test Branch'],
            'timezone' => 'UTC',
        ]);

        $this->assertEquals(201, $response->status());
    }
}
