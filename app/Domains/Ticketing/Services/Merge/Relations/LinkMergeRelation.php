<?php

namespace App\Domains\Ticketing\Services\Merge\Relations;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketLink;
use App\Domains\Ticketing\Services\Merge\TicketMergeRelation;

class LinkMergeRelation implements TicketMergeRelation
{
    public function move(Ticket $source, Ticket $target): void
    {
        $sourceLinks = TicketLink::where('source_ticket_id', $source->id)->get();

        foreach ($sourceLinks as $link) {
            $existing = TicketLink::where('source_ticket_id', $target->id)
                ->where('target_ticket_id', $link->target_ticket_id)
                ->where('relation', $link->relation)
                ->exists();

            if (! $existing) {
                TicketLink::create([
                    'source_ticket_id' => $target->id,
                    'target_ticket_id' => $link->target_ticket_id,
                    'relation' => $link->relation,
                    'created_by_user_id' => $link->created_by_user_id,
                ]);
            }
        }
    }

    public function key(): string
    {
        return 'links';
    }
}
