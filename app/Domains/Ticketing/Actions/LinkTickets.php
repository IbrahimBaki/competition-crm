<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\CannotLinkTicketToItselfException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketLink;
use App\Domains\Ticketing\Models\TicketLinkRelation;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class LinkTickets
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function __invoke(
        Ticket $source,
        Ticket $target,
        TicketLinkRelation $relation,
        User $actor,
    ): TicketLink {
        throw_if($source->id === $target->id, CannotLinkTicketToItselfException::class);

        $link = \DB::transaction(function () use ($source, $target, $relation, $actor) {
            $link = TicketLink::updateOrCreate(
                [
                    'source_ticket_id' => $source->id,
                    'target_ticket_id' => $target->id,
                    'relation' => $relation,
                ],
                [
                    'created_by_user_id' => $actor->id,
                ],
            );

            $inverse = $relation->inverse();

            TicketLink::updateOrCreate(
                [
                    'source_ticket_id' => $target->id,
                    'target_ticket_id' => $source->id,
                    'relation' => $inverse,
                ],
                [
                    'created_by_user_id' => $actor->id,
                ],
            );

            $this->recordEvent->handle($source, TicketEventType::Linked, $actor, [
                'target_uuid' => $target->uuid,
                'relation' => $relation->value,
            ]);

            $this->recordEvent->handle($target, TicketEventType::Linked, $actor, [
                'source_uuid' => $source->uuid,
                'relation' => $inverse->value,
            ]);

            return $link;
        });

        return $link;
    }
}
