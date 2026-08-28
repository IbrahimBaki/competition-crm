<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class UpdateTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    /**
     * Update ticket and record changed fields in history.
     * No event is written if nothing changed.
     */
    public function handle(
        Ticket $ticket,
        array $updates,
        ?User $actor = null,
    ): Ticket {
        $changes = $this->computeChanges($ticket, $updates);

        if (empty($changes)) {
            return $ticket;
        }

        $ticket->update($updates);

        $this->recordEvent->handle($ticket, TicketEventType::Updated, $actor, [
            'changes' => $changes,
        ]);

        return $ticket->fresh();
    }

    private function computeChanges(Ticket $ticket, array $updates): array
    {
        $changes = [];

        foreach ($updates as $field => $newValue) {
            $oldValue = $ticket->getAttribute($field);

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'from' => $oldValue,
                    'to' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
