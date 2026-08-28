<?php

namespace App\Domains\Ticketing\Services\Merge;

use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Support\Collection;

class TicketMergeRelationRegistry
{
    private Collection $relations;

    public function __construct()
    {
        $this->relations = collect();
    }

    public function register(TicketMergeRelation $relation): self
    {
        $this->relations->put($relation->key(), $relation);

        return $this;
    }

    public function moveAll(Ticket $source, Ticket $target): void
    {
        $this->relations->each(fn ($relation) => $relation->move($source, $target));
    }
}
