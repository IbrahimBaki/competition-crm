<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Models\PasswordReset;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
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

        $token = Str::random(64);
        PasswordReset::create([
            'id' => Str::uuid(),
            'email' => $user->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->postJson('/api/v1/auth/password/reset', [
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
            'token' => 'wrong-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertEquals(422, $response->status());
    }
}
