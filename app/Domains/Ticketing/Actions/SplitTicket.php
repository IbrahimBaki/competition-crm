<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketLink;
use App\Domains\Ticketing\Models\TicketLinkRelation;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class SplitTicket
{
    public function __construct(
        private readonly CreateTicket $createTicket,
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function __invoke(
        Ticket $parent,
        User $actor,
        string $subject,
        string $body,
        ?int $categoryId = null,
    ): Ticket {
        $child = \DB::transaction(function () use ($parent, $actor, $subject, $body, $categoryId) {
            $child = ($this->createTicket)(
                $parent->customer_id,
                $parent->department_id,
                $subject,
                $body,
                $actor,
                $categoryId ?? $parent->ticket_category_id,
            );

            $child->update(['parent_ticket_id' => $parent->id]);

            TicketLink::create([
                'source_ticket_id' => $parent->id,
                'target_ticket_id' => $child->id,
                'relation' => TicketLinkRelation::Related,
                'created_by_user_id' => $actor->id,
            ]);

            TicketLink::create([
                'source_ticket_id' => $child->id,
                'target_ticket_id' => $parent->id,
                'relation' => TicketLinkRelation::Related,
                'created_by_user_id' => $actor->id,
            ]);

            $this->recordEvent->handle($parent, TicketEventType::Split, $actor, [
                'child_uuid' => $child->uuid,
            ]);

            $this->recordEvent->handle($child, TicketEventType::Split, $actor, [
                'parent_uuid' => $parent->uuid,
            ]);

            return $child->fresh();
        });

        return $child;
    }
}
