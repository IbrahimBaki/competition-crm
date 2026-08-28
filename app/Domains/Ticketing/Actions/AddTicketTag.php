<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketTag;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class AddTicketTag
{
    public function __construct(
        private readonly TextNormaliser $normaliser,
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(Ticket $ticket, string $tagName, ?User $actor = null): TicketTag
    {
        $normalised = $this->normaliser->normaliseName($tagName);

        $tag = TicketTag::firstOrCreate(
            ['name_normalised' => $normalised],
            ['name' => $tagName]
        );

        $ticket->tags()->syncWithoutDetaching([$tag->id]);

        $this->recordEvent->handle($ticket, TicketEventType::TagAdded, $actor, [
            'tag_uuid' => $tag->uuid,
        ]);

        return $tag;
    }
}
