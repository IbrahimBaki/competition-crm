<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeactivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_deactivating_user_deletes_tokens(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->actingAs($admin)
            ->postJson("/api/v1/users/{$user->uuid}/deactivate");

        $this->assertFalse(DB::table('personal_access_tokens')
            ->where('tokenable_uuid', $user->uuid)
            ->exists());
    }

    public function test_deactivating_user_clears_sessions(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $user->getAuthIdentifier(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent',
            'payload' => base64_encode('test'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->postJson("/api/v1/users/{$user->uuid}/deactivate");

        $this->assertFalse(DB::table('sessions')
            ->where('user_id', $user->getAuthIdentifier())
            ->exists());
    }

    public function test_cannot_deactivate_self(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/users/{$admin->uuid}/deactivate");

        $this->assertEquals(409, $response->status());
        $this->assertEquals('cannot_deactivate_self', $response->json('error.code'));
    }

    public function test_cannot_deactivate_last_administrator(): void
    {
        $lastAdmin = User::factory()->create();
        $lastAdmin->roles()->attach(1);

        $response = $this->actingAs($lastAdmin)
            ->postJson("/api/v1/users/{$lastAdmin->uuid}/deactivate");

        $this->assertEquals(409, $response->status());
    }
}
