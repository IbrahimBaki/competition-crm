<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TwoFactorPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_unauthenticated_user_can_access_login(): void
    {
        $response = $this->getJson('/api/v1/auth/login');

        $this->assertEquals(405, $response->status()); // Method not allowed for GET
    }

    public function test_user_without_2fa_gets_403_when_policy_enabled(): void
    {
        DB::table('auth_settings')->updateOrInsert(
            ['id' => 1],
            ['require_two_factor' => true]
        );

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $this->assertEquals(403, $response->status());
        $this->assertEquals('two_factor_enrollment_required', $response->json('error.code'));
    }

    public function test_2fa_endpoints_accessible_without_confirmation(): void
    {
        DB::table('auth_settings')->updateOrInsert(
            ['id' => 1],
            ['require_two_factor' => true]
        );

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/auth/two-factor');

        $this->assertNotEquals(403, $response->status());
    }

    public function test_logout_accessible_without_2fa_confirmation(): void
    {
        DB::table('auth_settings')->updateOrInsert(
            ['id' => 1],
            ['require_two_factor' => true]
        );

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/auth/logout');

        $this->assertNotEquals(403, $response->status());
    }
}
