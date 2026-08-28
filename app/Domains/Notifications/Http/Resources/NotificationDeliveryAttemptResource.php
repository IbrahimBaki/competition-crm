<?php

namespace App\Domains\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationDeliveryAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->notification->uuid,
            'event_type' => $this->notification->event_type,
            'recipient_email' => $this->notification->recipient->email,
            'channel' => $this->channel,
            'outcome' => $this->outcome,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'attempted_at' => $this->attempted_at->toIso8601String(),
        ];
    }
}
