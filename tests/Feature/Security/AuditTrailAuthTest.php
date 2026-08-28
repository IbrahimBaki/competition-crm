<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Models\AuditLog;
use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditTrailAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    public function test_login_success_audited(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertTrue(
            AuditLog::where('action', 'login.success')
                ->where('target_id', $user->uuid)
                ->exists()
        );
    }

    public function test_login_failure_audited(): void
    {
        User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertTrue(
            AuditLog::where('action', 'login.failed')->exists()
        );
    }

    public function test_logout_audited(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/auth/logout');

        $this->assertTrue(
            AuditLog::where('action', 'logout')
                ->where('actor_uuid', $user->uuid)
                ->exists()
        );
    }

    public function test_user_invited_audited(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $this->actingAs($admin)
            ->postJson('/api/v1/users/invite', ['email' => 'newuser@example.com']);

        $this->assertTrue(
            AuditLog::where('action', 'user.invited')->exists()
        );
    }

    public function test_user_deactivated_audited(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $user = User::factory()->create();

        $this->actingAs($admin)
            ->postJson("/api/v1/users/{$user->uuid}/deactivate");

        $this->assertTrue(
            AuditLog::where('action', 'user.deactivated')
                ->where('target_id', $user->uuid)
                ->where('actor_uuid', $admin->uuid)
                ->exists()
        );
    }
}
