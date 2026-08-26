<?php

namespace App\Domains\Customers\Http\Resources;

use App\Domains\Customers\Services\Timeline\TimelineEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineEntryResource extends JsonResource
{
    public function __construct(private TimelineEntry $entry)
    {
        parent::__construct($entry);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->entry->id,
            'source' => $this->entry->source,
            'type' => $this->entry->type,
            'occurred_at' => $this->entry->occurredAt->format(\DateTimeInterface::ATOM),
            'actor_uuid' => $this->entry->actorUuid,
            'payload' => $this->entry->payload,
        ];
    }
}
