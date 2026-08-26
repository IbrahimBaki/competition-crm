<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class ChangeTicketPriority
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(Ticket $ticket, TicketPriority $priority, ?User $actor = null): Ticket
    {
        $old = $ticket->priority;
        $ticket->update(['priority' => $priority]);

        $this->recordEvent->handle($ticket, TicketEventType::PriorityChanged, $actor, [
            'from' => $old->value,
            'to' => $priority->value,
        ]);

        return $ticket->fresh();
    }
}
