<?php

namespace App\Domains\Channels\WebForm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebFormStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ticket_reference' => $this->ticket?->reference,
            'status' => $this->ticket?->status?->label,
            'last_updated' => $this->ticket?->updated_at,
        ];
    }
}
