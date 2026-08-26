<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketTag;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class RemoveTicketTag
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(Ticket $ticket, TicketTag $tag, ?User $actor = null): void
    {
        $ticket->tags()->detach($tag->id);

        $this->recordEvent->handle($ticket, TicketEventType::TagRemoved, $actor, [
            'tag_uuid' => $tag->uuid,
        ]);
    }
}
