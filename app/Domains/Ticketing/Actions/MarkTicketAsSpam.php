<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarkTicketAsSpam
{
    public function __construct(
        private readonly ChangeTicketStatus $changeStatus,
        private readonly RecordTicketEvent $recordEvent,
        private readonly SlaClockHooks $slaHooks,
    ) {}

    public function __invoke(Ticket $ticket, User $actor, string $reason): Ticket
    {
        $spamStatus = TicketStatusDefinition::query()
            ->where('lifecycle_type', 'spam')
            ->firstOrFail();

        $ticket = ($this->changeStatus)($ticket, $spamStatus, $actor, $reason);

        $ticket = DB::transaction(function () use ($ticket, $actor) {
            $this->recordEvent->handle($ticket, TicketEventType::MarkedSpam, $actor);

            $this->slaHooks->ticketCancelled($ticket, 'spam', CarbonImmutable::now('UTC'));

            return $ticket;
        });

        return $ticket;
    }
}
