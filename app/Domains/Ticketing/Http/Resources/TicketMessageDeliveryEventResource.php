<?php

namespace App\Domains\Ticketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageDeliveryEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'from_state' => $this->from_state?->value,
            'to_state' => $this->to_state->value,
            'reason' => $this->reason,
            'occurred_at' => $this->occurred_at->toIso8601String(),
        ];
    }
}
