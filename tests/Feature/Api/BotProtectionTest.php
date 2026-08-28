<?php

namespace Tests\Feature\Api;

use App\Domains\Security\Models\Role;
use App\Models\User;
use App\Support\Http\BotProtection\BotProtectionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BotProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoint_with_failing_bot_guard_returns_422(): void
    {
        $failingGuard = new class implements BotProtectionGuard
        {
            public function verify(Request $request): bool
            {
                return false;
            }
        };

        $this->app->bind(BotProtectionGuard::class, fn () => $failingGuard);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'bot_protection_failed');
    }

    public function test_public_endpoint_with_passing_bot_guard_proceeds(): void
    {
        $passingGuard = new class implements BotProtectionGuard
        {
            public function verify(Request $request): bool
            {
                return true;
            }
        };

        $this->app->bind(BotProtectionGuard::class, fn () => $passingGuard);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Should fail auth, not bot protection
        $this->assertNotEquals('bot_protection_failed', $response->json('error.code'));
    }

    public function test_authenticated_routes_bypass_bot_protection(): void
    {
        $failingGuard = new class implements BotProtectionGuard
        {
            public function verify(Request $request): bool
            {
                return false;
            }
        };

        $this->app->bind(BotProtectionGuard::class, fn () => $failingGuard);

        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => Role::ADMINISTRATOR]);
        $user->roles()->attach($adminRole);

        $response = $this->actingAs($user)
            ->get('/api/v1/auth/me');

        // Should succeed (200 or similar), not be blocked by bot protection
        $this->assertNotEquals(422, $response->status());
    }

    public function test_health_probes_bypass_bot_protection(): void
    {
        $failingGuard = new class implements BotProtectionGuard
        {
            public function verify(Request $request): bool
            {
                return false;
            }
        };

        $this->app->bind(BotProtectionGuard::class, fn () => $failingGuard);

        $response = $this->get('/api/v1/health/live');

        // Health probes should work even if bot protection fails
        $this->assertNotEquals(422, $response->status());
    }
}
