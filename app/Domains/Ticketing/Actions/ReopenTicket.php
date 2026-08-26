<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReopenTicket
{
    public function __construct(
        private readonly ChangeTicketStatus $changeStatus,
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function __invoke(Ticket $ticket, User $actor, string $reason): Ticket
    {
        $openStatus = TicketStatusDefinition::query()
            ->where('lifecycle_type', 'open')
            ->firstOrFail();

        $ticket = ($this->changeStatus)($ticket, $openStatus, $actor, $reason);

        $ticket = DB::transaction(function () use ($ticket, $actor) {
            $ticket->increment('reopened_count');
            $ticket->refresh();

            $this->recordEvent->handle($ticket, TicketEventType::Reopened, $actor);

            return $ticket;
        });

        return $ticket;
    }
}
