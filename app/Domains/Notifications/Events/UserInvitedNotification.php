<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Notifications\Enums\NotificationEventType;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Str;

final readonly class UserInvitedNotification implements NotifiableEvent, ShouldDispatchAfterCommit
{
    private string $id;

    public function __construct(
        private string $invitationToken,
        private string $inviteeEmail,
        private int $invitedByUserId,
        private array $recipientUserIds,
    ) {
        $this->id = Str::uuid()->toString();
    }

    public function eventId(): string
    {
        return $this->id;
    }

    public function eventType(): NotificationEventType
    {
        return NotificationEventType::UserInvited;
    }

    public function recipientIds(): array
    {
        return $this->recipientUserIds;
    }

    public function payload(): array
    {
        return [
            'invitation_token' => $this->invitationToken,
            'invitee_email' => $this->inviteeEmail,
            'invited_by_user_id' => $this->invitedByUserId,
        ];
    }
}
