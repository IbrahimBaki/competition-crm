<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use App\Models\User;
use Carbon\CarbonImmutable;

class ChangeTicketPriority
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly SlaClockHooks $slaHooks,
    ) {}

    public function handle(Ticket $ticket, TicketPriority $priority, ?User $actor = null): Ticket
    {
        $old = $ticket->priority;
        $ticket->update(['priority' => $priority]);

        $this->recordEvent->handle($ticket, TicketEventType::PriorityChanged, $actor, [
            'from' => $old->value,
            'to' => $priority->value,
        ]);

        $now = CarbonImmutable::now('UTC');
        $this->slaHooks->classificationChanged($ticket, $now);

        app(TicketAutomationHooks::class)->priorityChanged($ticket->fresh(), $actor);

        return $ticket->fresh();
    }
}
