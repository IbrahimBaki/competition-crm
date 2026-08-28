<?php

namespace App\Domains\Ticketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'source_ticket_id' => $this->source?->uuid,
            'target_ticket_id' => $this->target?->uuid,
            'relation' => $this->relation->value,
            'created_by_user_id' => $this->createdBy?->uuid,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
