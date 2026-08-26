<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\TicketNotSpamException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RestoreTicketFromSpam
{
    public function __construct(
        private readonly ChangeTicketStatus $changeStatus,
        private readonly RecordTicketEvent $recordEvent,
        private readonly SlaClockHooks $slaHooks,
    ) {}

    public function __invoke(Ticket $ticket, User $actor, string $reason): Ticket
    {
        throw_if($ticket->lifecycleType()->value !== 'spam', TicketNotSpamException::class);

        $openStatus = TicketStatusDefinition::query()
            ->where('lifecycle_type', 'open')
            ->firstOrFail();

        $ticket = ($this->changeStatus)($ticket, $openStatus, $actor, $reason);

        $ticket = DB::transaction(function () use ($ticket, $actor) {
            $this->recordEvent->handle($ticket, TicketEventType::RestoredFromSpam, $actor);

            $this->slaHooks->ticketRestored($ticket, CarbonImmutable::now('UTC'));

            return $ticket;
        });

        return $ticket;
    }
}
