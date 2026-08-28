<?php

namespace App\Domains\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'event_type' => $this->event_type,
            'channel' => $this->channel,
            'state' => $this->state,
            'subject' => $this->subject,
            'body' => $this->body,
            'read_at' => $this->read_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
