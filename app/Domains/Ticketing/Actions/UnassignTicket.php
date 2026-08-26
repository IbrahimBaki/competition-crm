<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class UnassignTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(Ticket $ticket, ?User $actor = null): Ticket
    {
        $ticket->update(['assigned_user_id' => null]);

        $this->recordEvent->handle($ticket, TicketEventType::Unassigned, $actor);

        return $ticket->fresh();
    }
}
