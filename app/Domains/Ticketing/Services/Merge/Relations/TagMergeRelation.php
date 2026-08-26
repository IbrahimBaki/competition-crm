<?php

namespace App\Domains\Ticketing\Services\Merge\Relations;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Services\Merge\TicketMergeRelation;

class TagMergeRelation implements TicketMergeRelation
{
    public function move(Ticket $source, Ticket $target): void
    {
        $targetTagIds = $target->tags()->pluck('id')->toArray();

        foreach ($source->tags()->get() as $tag) {
            if (! in_array($tag->id, $targetTagIds, true)) {
                $target->tags()->attach($tag->id);
            }
        }
    }

    public function key(): string
    {
        return 'tags';
    }
}
