<?php

namespace App\Domains\Ticketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'parent_id' => $this->parent?->uuid,
            'depth' => $this->depth,
            'is_active' => $this->is_active,
            'children' => TicketCategoryResource::collection($this->whenLoaded('children')),
            'fields' => TicketCategoryFieldResource::collection($this->whenLoaded('fields')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
