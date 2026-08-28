<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_catalogue_returns_all_keys_grouped_by_module(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/permissions/catalogue');

        $this->assertEquals(200, $response->status());
        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('admin', $data);
        $this->assertArrayHasKey('org', $data);
        $this->assertArrayHasKey('tickets', $data);

        $adminKeys = array_column($data['admin'], 'key');
        $this->assertContains(PermissionKey::ADMIN_ROLES_MANAGE, $adminKeys);
        $this->assertContains(PermissionKey::ADMIN_USERS_MANAGE, $adminKeys);
        $this->assertContains(PermissionKey::ADMIN_STRUCTURE_MANAGE, $adminKeys);
    }

    public function test_permission_catalogue_includes_all_declared_keys(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/permissions/catalogue');
        $data = $response->json('data');

        $allKeysInResponse = [];
        foreach ($data as $module => $keys) {
            foreach ($keys as $keyData) {
                $allKeysInResponse[] = $keyData['key'];
            }
        }

        $declaredKeys = PermissionKey::all();
        sort($declaredKeys);
        sort($allKeysInResponse);

        $this->assertEquals($declaredKeys, $allKeysInResponse);
    }
}
