<?php

namespace Tests\Unit\Security;

use App\Domains\Security\Actions\AcceptInvitation;
use App\Domains\Security\Exceptions\ExpiredInvitationException;
use App\Domains\Security\Exceptions\InvalidInvitationException;
use App\Domains\Security\Models\UserInvitation;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    private function strongPassword(): string
    {
        return Str::random(12).'1A!';
    }

    public function test_rejects_expired_invitation(): void
    {
        $invitation = UserInvitation::create([
            'id' => Str::uuid(),
            'email' => 'newuser@example.com',
            'token_hash' => hash('sha256', 'test-token'),
            'expires_at' => now()->subHour(),
        ]);

        $action = app(AcceptInvitation::class);

        $this->expectException(ExpiredInvitationException::class);
        $action->execute('test-token', $this->strongPassword());
    }

    public function test_rejects_already_accepted_invitation(): void
    {
        $invitation = UserInvitation::create([
            'id' => Str::uuid(),
            'email' => 'newuser@example.com',
            'token_hash' => hash('sha256', 'test-token'),
            'expires_at' => now()->addHour(),
            'accepted_at' => now(),
        ]);

        $action = app(AcceptInvitation::class);

        $this->expectException(InvalidInvitationException::class);
        $action->execute('test-token', $this->strongPassword());
    }

    public function test_creates_user_with_hashed_password(): void
    {
        $invitation = UserInvitation::create([
            'id' => Str::uuid(),
            'email' => 'newuser@example.com',
            'token_hash' => hash('sha256', 'test-token'),
            'expires_at' => now()->addHour(),
        ]);

        $action = app(AcceptInvitation::class);
        $password = $this->strongPassword();

        $user = $action->execute('test-token', $password);

        $this->assertTrue(Hash::check($password, $user->password));
    }
}
