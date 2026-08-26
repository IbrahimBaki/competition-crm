<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Models\AuditLog;
use App\Domains\Security\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogListEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_user_cannot_access_audit_logs(): void
    {
        $response = $this->getJson('/api/v1/audit-logs');
        $this->assertEquals(401, $response->status());
    }

    public function test_authenticated_user_without_permission_cannot_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/audit-logs');
        $this->assertEquals(403, $response->status());
    }

    public function test_administrator_can_access_audit_logs(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach($this->getAdministratorRole()->id);

        AuditLog::create([
            'id' => 'test-id-1',
            'actor_uuid' => 'actor-uuid',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-1',
            'before' => null,
            'after' => ['name' => 'Test'],
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/audit-logs');

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('meta', $response->json());
        $this->assertArrayHasKey('links', $response->json());
        $this->assertCount(1, $response->json('data'));
    }

    public function test_audit_logs_returns_list_results(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach($this->getAdministratorRole()->id);

        $now = now();
        AuditLog::create([
            'id' => 'test-id-1',
            'actor_uuid' => 'actor-1',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-1',
            'recorded_at' => $now->subHours(2),
        ]);

        AuditLog::create([
            'id' => 'test-id-2',
            'actor_uuid' => 'actor-2',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-2',
            'recorded_at' => $now,
        ]);

        // Test that list endpoint returns results
        $response = $this->actingAs($admin)->getJson('/api/v1/audit-logs');
        $this->assertEquals(200, $response->status());
        $this->assertCount(2, $response->json('data'));
        $this->assertNotNull($response->json('data.0.recorded_at'));
    }

    public function test_audit_logs_can_be_filtered_by_actor_uuid(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach($this->getAdministratorRole()->id);

        AuditLog::create([
            'id' => 'test-id-1',
            'actor_uuid' => 'actor-1',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-1',
            'recorded_at' => now(),
        ]);

        AuditLog::create([
            'id' => 'test-id-2',
            'actor_uuid' => 'actor-2',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-2',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/audit-logs?filter[actor_uuid][eq]=actor-1');

        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('actor-1', $response->json('data.0.actor_uuid'));
    }

    public function test_audit_logs_can_be_filtered_by_target_type(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach($this->getAdministratorRole()->id);

        AuditLog::create([
            'id' => 'test-id-1',
            'actor_uuid' => 'actor-1',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-1',
            'recorded_at' => now(),
        ]);

        AuditLog::create([
            'id' => 'test-id-2',
            'actor_uuid' => 'actor-2',
            'action' => 'test.action',
            'target_type' => 'Role',
            'target_id' => 'role-1',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/audit-logs?filter[target_type][eq]=User');

        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('User', $response->json('data.0.target_type'));
    }

    public function test_audit_logs_can_be_filtered_by_action(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach($this->getAdministratorRole()->id);

        AuditLog::create([
            'id' => 'test-id-1',
            'actor_uuid' => 'actor-1',
            'action' => 'security.role.created',
            'target_type' => 'Role',
            'target_id' => 'role-1',
            'recorded_at' => now(),
        ]);

        AuditLog::create([
            'id' => 'test-id-2',
            'actor_uuid' => 'actor-2',
            'action' => 'security.role.updated',
            'target_type' => 'Role',
            'target_id' => 'role-2',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/audit-logs?filter[action][eq]=security.role.created');

        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('security.role.created', $response->json('data.0.action'));
    }

    public function test_pagination_works_correctly(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach($this->getAdministratorRole()->id);

        for ($i = 0; $i < 30; $i++) {
            AuditLog::create([
                'id' => "test-id-$i",
                'actor_uuid' => 'actor-1',
                'action' => 'test.action',
                'target_type' => 'User',
                'target_id' => "user-$i",
                'recorded_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs($admin)->getJson('/api/v1/audit-logs?page=1&per_page=10');

        $this->assertEquals(200, $response->status());
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.page'));
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(30, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.total_pages'));
    }

    private function getAdministratorRole()
    {
        return Role::where('name', 'administrator')->first();
    }
}
