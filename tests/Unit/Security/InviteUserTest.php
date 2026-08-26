<?php

namespace Tests\Unit\Security;

use App\Domains\Security\Actions\InviteUser;
use App\Domains\Security\Exceptions\InvitationAlreadyPendingException;
use App\Domains\Security\Models\AuditLog;
use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    public function test_token_is_stored_as_sha256_hash(): void
    {
        $actor = User::factory()->create();
        $action = app(InviteUser::class);

        [$invitation, $rawToken] = $action->execute($actor, 'newuser@example.com');

        $expected = hash('sha256', $rawToken);
        $this->assertEquals($expected, $invitation->token_hash);
    }

    public function test_invitation_expires_at_72_hours(): void
    {
        $actor = User::factory()->create();
        $action = app(InviteUser::class);

        [$invitation] = $action->execute($actor, 'newuser@example.com');

        $expectedExpiry = now()->addHours(72);
        $this->assertTrue($invitation->expires_at->diffInHours($expectedExpiry) < 1);
    }

    public function test_user_invited_audit_entry_recorded(): void
    {
        $actor = User::factory()->create();
        $action = app(InviteUser::class);

        $action->execute($actor, 'newuser@example.com');

        $this->assertTrue(
            AuditLog::where('action', 'user.invited')
                ->where('actor_uuid', $actor->uuid)
                ->exists()
        );
    }

    public function test_rejects_second_invite_while_pending(): void
    {
        $actor = User::factory()->create();
        $action = app(InviteUser::class);

        $action->execute($actor, 'newuser@example.com');

        $this->expectException(InvitationAlreadyPendingException::class);
        $action->execute($actor, 'newuser@example.com');
    }
}
