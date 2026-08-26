<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_me_returns_permission_keys(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'editor',
            'display_name' => ['ar' => 'محرر', 'en' => 'Editor'],
        ]);
        $role->permissions()->createMany([
            ['permission_key' => PermissionKey::ORG_BRANCHES_VIEW_ANY],
            ['permission_key' => PermissionKey::ORG_DEPARTMENTS_VIEW_ANY],
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('permission_keys', $response->json('data'));
        $this->assertContains(PermissionKey::ORG_BRANCHES_VIEW_ANY, $response->json('data.permission_keys'));
        $this->assertContains(PermissionKey::ORG_DEPARTMENTS_VIEW_ANY, $response->json('data.permission_keys'));
    }

    public function test_auth_me_does_not_return_role_name(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'editor',
            'display_name' => ['ar' => 'محرر', 'en' => 'Editor'],
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $this->assertEquals(200, $response->status());
        $this->assertArrayNotHasKey('role_name', $response->json('data'));
        $this->assertArrayNotHasKey('role', $response->json('data'));
        $this->assertArrayNotHasKey('roles', $response->json('data'));
    }
}
