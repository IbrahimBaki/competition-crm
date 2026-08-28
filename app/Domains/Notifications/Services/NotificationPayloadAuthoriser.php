<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Enums\NotificationEventType;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class NotificationPayloadAuthoriser
{
    public function authorizeAndFilter(
        User $recipient,
        NotificationEventType $eventType,
        array $payload,
    ): ?array {
        return match ($eventType) {
            NotificationEventType::SlaWarning,
            NotificationEventType::SlaBreach,
            NotificationEventType::TicketAssigned,
            NotificationEventType::TicketTransferred,
            NotificationEventType::TicketEscalated,
            NotificationEventType::TicketMessagePosted => $this->filterTicketEventPayload($recipient, $payload),
            NotificationEventType::UserInvited => $payload,
        };
    }

    private function filterTicketEventPayload(User $recipient, array $payload): ?array
    {
        if (! isset($payload['ticket_id'])) {
            return $payload;
        }

        $ticket = Ticket::query()->find($payload['ticket_id']);
        if (! $ticket) {
            return $payload;
        }

        try {
            $recipient->authorize('view', $ticket);
        } catch (AuthorizationException) {
            return null;
        }

        return $payload;
    }
}
