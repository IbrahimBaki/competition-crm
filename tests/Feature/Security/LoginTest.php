<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('meta', $response->json());
        $this->assertArrayHasKey('token', $response->json('meta'));
    }

    public function test_invalid_credentials_rejected(): void
    {
        User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertEquals(401, $response->status());
        $this->assertEquals('account_deactivated', $response->json('error.code'));
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'deactivated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertEquals(401, $response->status());
        $this->assertEquals('account_deactivated', $response->json('error.code'));
    }

    public function test_account_locked_after_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'failed_login_count' => 10,
            'locked_until' => now()->addMinutes(30),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertEquals(423, $response->status());
        $this->assertEquals('account_locked', $response->json('error.code'));
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $this->assertEquals(204, $response->status());
    }
}
