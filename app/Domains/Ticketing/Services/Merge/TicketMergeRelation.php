<?php

namespace App\Domains\Ticketing\Services\Merge;

use App\Domains\Ticketing\Models\Ticket;

interface TicketMergeRelation
{
    public function move(Ticket $source, Ticket $target): void;

    public function key(): string;
}
