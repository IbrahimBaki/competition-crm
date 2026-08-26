<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class AssignTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(Ticket $ticket, User $assignee, ?User $actor = null): Ticket
    {
        $ticket->update(['assigned_user_id' => $assignee->id]);

        $this->recordEvent->handle($ticket, TicketEventType::Assigned, $actor, [
            'user_uuid' => $assignee->uuid ?? $assignee->id,
        ]);

        return $ticket->fresh();
    }
}
