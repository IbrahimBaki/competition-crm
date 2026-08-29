<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
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

        $response = $this->withHeader('Origin', config('app.frontend_url', 'http://app.competition-crm.azmsquad.localhost'))->postJson('/api/v1/auth/login', [
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

        $response = $this->withHeader('Origin', 'http://competition-crm.azmsquad.localhost:5174')->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertEquals(401, $response->status());
        $this->assertEquals('account_deactivated', $response->json('error.code'));
    }

    public function test_user_with_two_factor_does_not_receive_session_or_token_before_challenge(): void
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['recovery-code-1'],
        ]);

        $response = $this->withHeader('Origin', 'http://competition-crm.azmsquad.localhost:5174')->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('meta.two_factor_required', true)
            ->assertJsonMissingPath('meta.token');
        $this->assertGuest('web');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $this->postJson('/api/v1/auth/two-factor/challenge', ['code' => '000000'])
            ->assertStatus(422);
        $this->assertGuest('web');

        $this->postJson('/api/v1/auth/two-factor/challenge', [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertOk()->assertJsonStructure(['meta' => ['token']]);

        $this->assertAuthenticatedAs($user, 'web');
        $this->getJson('/api/v1/auth/me')->assertOk();
    }

    public function test_two_factor_recovery_code_completes_challenge_once(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['one-use-code'],
        ]);

        $this->withHeader('Origin', 'http://competition-crm.azmsquad.localhost:5174')->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertJsonPath('meta.two_factor_required', true);

        $this->postJson('/api/v1/auth/two-factor/challenge', ['code' => 'one-use-code'])
            ->assertOk();

        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);
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
