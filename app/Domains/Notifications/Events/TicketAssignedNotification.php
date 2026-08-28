<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Notifications\Enums\NotificationEventType;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Str;

final readonly class TicketAssignedNotification implements NotifiableEvent, ShouldDispatchAfterCommit
{
    private string $id;

    public function __construct(
        private int $ticketId,
        private string $ticketReference,
        private string $customerName,
        private string $customerEmail,
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
        return NotificationEventType::TicketAssigned;
    }

    public function recipientIds(): array
    {
        return $this->recipientUserIds;
    }

    public function payload(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'ticket_reference' => $this->ticketReference,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
        ];
    }
}
