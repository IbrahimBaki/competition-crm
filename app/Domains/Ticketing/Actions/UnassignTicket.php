<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\TicketIsReadOnlyException;
use App\Domains\Ticketing\Exceptions\TicketNotAssignedException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\Concurrency\TicketVersionGuard;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class UnassignTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly TicketVersionGuard $versionGuard,
    ) {}

    public function handle(Ticket $ticket, ?User $actor = null): Ticket
    {
        if ($ticket->isMerged() || $ticket->isSpam()) {
            throw new TicketIsReadOnlyException('Ticket is read-only');
        }

        if ($ticket->assigned_user_id === null) {
            throw new TicketNotAssignedException('Ticket is not assigned');
        }

        $previousAssignee = $ticket->assignee;
        $previousAssigneeUuid = $previousAssignee?->uuid;

        $ticket = $this->versionGuard->bump($ticket, [
            'assigned_user_id' => null,
            'assigned_at' => null,
        ]);

        $this->recordEvent->handle($ticket, TicketEventType::Unassigned, $actor, [
            'previous_user_uuid' => $previousAssigneeUuid,
        ]);

        return $ticket;
    }
}
