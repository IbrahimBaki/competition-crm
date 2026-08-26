<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketTag;

class SyncTicketTags
{
    public function __construct(
        private readonly TextNormaliser $normaliser,
    ) {}

    /**
     * @param  array<string>  $tagNames
     */
    public function handle(Ticket $ticket, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }

            $normalised = $this->normaliser->normaliseName($name);

            $tag = TicketTag::firstOrCreate(
                ['name_normalised' => $normalised],
                ['name' => $name]
            );

            $tagIds[] = $tag->id;
        }

        $ticket->tags()->sync($tagIds);
    }
}
