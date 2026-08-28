<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    public function test_forgot_password_returns_202(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $user->email,
        ]);

        $this->assertEquals(202, $response->status());
    }

    public function test_deactivated_user_forgot_request_returns_202(): void
    {
        $user = User::factory()->create([
            'deactivated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $user->email,
        ]);

        $this->assertEquals(202, $response->status());
    }

    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        // CompletePasswordReset delegates to Laravel's password broker, so the
        // token has to come from the broker rather than a hand-rolled row.
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertEquals(204, $response->status());

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_reset_password_with_wrong_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email,
            'token' => 'wrong-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertEquals(422, $response->status());
    }
}
