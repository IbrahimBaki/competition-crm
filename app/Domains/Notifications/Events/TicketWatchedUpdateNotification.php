<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Notifications\Enums\NotificationEventType;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Str;

final readonly class TicketWatchedUpdateNotification implements NotifiableEvent, ShouldDispatchAfterCommit
{
    private string $id;

    public function __construct(
        private int $ticketId,
        private string $ticketReference,
        private array $watcherUserIds,
    ) {
        $this->id = Str::uuid()->toString();
    }

    public function eventId(): string
    {
        return $this->id;
    }

    public function eventType(): NotificationEventType
    {
        return NotificationEventType::TicketWatchedUpdate;
    }

    public function recipientIds(): array
    {
        return $this->watcherUserIds;
    }

    public function payload(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'ticket_reference' => $this->ticketReference,
        ];
    }
}
