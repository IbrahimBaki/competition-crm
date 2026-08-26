<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\TicketIsReadOnlyException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\Concurrency\TicketVersionGuard;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class AssignTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly TicketVersionGuard $versionGuard,
    ) {}

    public function handle(Ticket $ticket, User $assignee, ?User $actor = null, ?int $expectedVersion = null): Ticket
    {
        if ($ticket->isMerged() || $ticket->isSpam()) {
            throw new TicketIsReadOnlyException('Ticket is read-only');
        }

        $this->versionGuard->assert($ticket, $expectedVersion);

        $previousAssigneeId = $ticket->assigned_user_id;
        $previousAssigneeUuid = null;
        if ($previousAssigneeId) {
            $previousAssignee = User::find($previousAssigneeId);
            $previousAssigneeUuid = $previousAssignee?->uuid;
        }

        $ticket = $this->versionGuard->bump($ticket, [
            'assigned_user_id' => $assignee->id,
            'assigned_at' => now(),
        ], $expectedVersion);

        $this->recordEvent->handle($ticket, TicketEventType::Assigned, $actor, [
            'user_uuid' => $assignee->uuid ?? $assignee->id,
            'previous_user_uuid' => $previousAssigneeUuid,
        ]);

        return $ticket;
    }
}
