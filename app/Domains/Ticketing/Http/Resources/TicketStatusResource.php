<?php

namespace App\Domains\Ticketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'key' => $this->key,
            'name' => $this->name,
            'lifecycle_type' => $this->lifecycle_type->value,
            'is_default' => $this->is_default,
            'is_system' => $this->is_system,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'stops_sla_clock' => $this->stopsSlaClock(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
