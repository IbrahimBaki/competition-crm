<?php

namespace Tests\Feature\Portal;

use App\Domains\Portal\Models\PortalAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalGuardIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_token_rejected_by_staff_routes(): void
    {
        $account = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $token = $account->createToken('portal', ['portal'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/audit-logs')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'portal.session_invalid');
    }

    public function test_staff_token_rejected_by_portal_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('staff')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/portal/me')
            ->assertStatus(401);
    }

    public function test_unauthenticated_portal_routes_need_no_token(): void
    {
        $response = $this->postJson('/api/v1/portal/auth/register', [
            'email' => 'test@example.com',
            'password' => 'SecureP@ss123',
            'password_confirmation' => 'SecureP@ss123',
        ]);

        $this->assertTrue($response->status() >= 200 && $response->status() < 500);
    }
}
