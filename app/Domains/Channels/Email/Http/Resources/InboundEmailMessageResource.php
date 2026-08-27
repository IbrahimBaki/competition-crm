<?php

namespace App\Domains\Channels\Email\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InboundEmailMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'provider' => $this->provider,
            'message_id' => $this->message_id,
            'from_address' => $this->from_address,
            'subject' => $this->subject,
            'classification' => $this->classification?->value,
            'state' => $this->state?->value,
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'received_at' => $this->received_at->toIso8601String(),
            'processed_at' => $this->processed_at?->toIso8601String(),
            'ticket_id' => $this->ticket?->uuid,
        ];
    }
}
