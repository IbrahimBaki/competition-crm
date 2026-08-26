<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\InvitationAlreadyPendingException;
use App\Domains\Security\Mail\UserInvitedMail;
use App\Domains\Security\Models\UserInvitation;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteUser
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, string $email): array
    {
        $pendingInvitation = UserInvitation::where('email', $email)
            ->whereNull('accepted_at')
            ->whereDate('expires_at', '>=', now())
            ->first();

        if ($pendingInvitation) {
            throw new InvitationAlreadyPendingException("An invitation is already pending for {$email}");
        }

        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        $invitation = UserInvitation::create([
            'id' => Str::uuid(),
            'email' => $email,
            'invited_by_uuid' => $actor->uuid,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addHours(72),
        ]);

        $this->auditLogger->record($actor, 'user.invited', $invitation, null, ['email' => $email]);

        Mail::queue(new UserInvitedMail($invitation, $rawToken));

        return [$invitation, $rawToken];
    }
}
