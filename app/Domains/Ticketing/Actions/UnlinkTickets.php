<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketLink;
use App\Domains\Ticketing\Models\TicketLinkRelation;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class UnlinkTickets
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function __invoke(
        Ticket $source,
        Ticket $target,
        TicketLinkRelation $relation,
        User $actor,
    ): void {
        \DB::transaction(function () use ($source, $target, $relation, $actor) {
            TicketLink::where('source_ticket_id', $source->id)
                ->where('target_ticket_id', $target->id)
                ->where('relation', $relation)
                ->delete();

            $inverse = $relation->inverse();

            TicketLink::where('source_ticket_id', $target->id)
                ->where('target_ticket_id', $source->id)
                ->where('relation', $inverse)
                ->delete();

            $this->recordEvent->handle($source, TicketEventType::Unlinked, $actor, [
                'target_uuid' => $target->uuid,
                'relation' => $relation->value,
            ]);

            $this->recordEvent->handle($target, TicketEventType::Unlinked, $actor, [
                'source_uuid' => $source->uuid,
                'relation' => $inverse->value,
            ]);
        });
    }
}
