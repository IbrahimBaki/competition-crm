<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\InvitationAlreadyAcceptedException;
use App\Domains\Security\Exceptions\InvitationExpiredException;
use App\Domains\Security\Models\UserInvitation;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcceptInvitation
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(string $rawToken, string $name, string $password): User
    {
        return DB::transaction(function () use ($rawToken, $name, $password) {
            $tokenHash = hash('sha256', $rawToken);

            $invitation = UserInvitation::where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (! $invitation) {
                throw new InvitationExpiredException('Invitation not found or expired.');
            }

            if ($invitation->accepted_at !== null) {
                throw new InvitationAlreadyAcceptedException('Invitation has already been accepted.');
            }

            if ($invitation->expires_at->isPast()) {
                throw new InvitationExpiredException('Invitation has expired.');
            }

            $user = User::create([
                'uuid' => Str::uuid(),
                'name' => $name,
                'email' => $invitation->email,
                'password' => Hash::make($password),
            ]);

            $invitation->update([
                'accepted_at' => now(),
                'accepted_user_uuid' => $user->uuid,
            ]);

            $this->auditLogger->record(null, 'user.activated', $user, null, ['email' => $user->email]);

            return $user;
        });
    }
}
