<?php

namespace App\Domains\Ticketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketCategoryFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
            'is_required' => $this->is_required,
            'position' => $this->position,
        ];
    }
}
