<?php

namespace App\Domains\Ticketing\Services\Merge\Relations;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Services\Merge\TicketMergeRelation;

class MessageMergeRelation implements TicketMergeRelation
{
    public function move(Ticket $source, Ticket $target): void
    {
        TicketMessage::where('ticket_id', $source->id)
            ->update(['ticket_id' => $target->id]);
    }

    public function key(): string
    {
        return 'messages';
    }
}
