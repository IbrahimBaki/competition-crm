<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Mail\UserInvitedMail;
use App\Domains\Security\Models\UserInvitation;
use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
        Mail::fake();
    }

    public function test_admin_can_invite_user_by_email(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(
            User::where('email', 'test@example.com')->value('id') ? 1 : 1
        );

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/users/invite', ['email' => 'newuser@example.com']);

        $this->assertEquals(201, $response->status());
        $this->assertTrue(UserInvitation::where('email', 'newuser@example.com')->exists());
        Mail::assertQueued(UserInvitedMail::class);
    }

    public function test_invitation_expires_after_72_hours(): void
    {
        $invitation = UserInvitation::create([
            'id' => Str::uuid(),
            'email' => 'test@example.com',
            'invited_by_uuid' => User::first()->uuid,
            'token_hash' => hash('sha256', 'test-token'),
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->postJson('/api/v1/invitations/test-token/accept', [
            'name' => 'Test User',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertEquals('invitation_expired', $response->json('error.code'));
    }

    public function test_invitee_can_accept_and_set_password(): void
    {
        $token = 'test-token-64-bytes-long';
        UserInvitation::create([
            'id' => Str::uuid(),
            'email' => 'invitee@example.com',
            'invited_by_uuid' => User::first()->uuid,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(72),
        ]);

        $response = $this->postJson('/api/v1/invitations/'.$token.'/accept', [
            'name' => 'New User',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertTrue(User::where('email', 'invitee@example.com')->exists());
    }

    public function test_weak_password_rejected(): void
    {
        $token = 'test-token-64-bytes-long';
        UserInvitation::create([
            'id' => Str::uuid(),
            'email' => 'weak@example.com',
            'invited_by_uuid' => User::first()->uuid,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(72),
        ]);

        $response = $this->postJson('/api/v1/invitations/'.$token.'/accept', [
            'name' => 'User',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $this->assertEquals(422, $response->status());
    }
}
