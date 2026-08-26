<?php

namespace App\Domains\Ticketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'type' => $this->type?->value,
            'actor_id' => $this->actor?->uuid,
            'payload' => $this->payload,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
        ];
    }
}
