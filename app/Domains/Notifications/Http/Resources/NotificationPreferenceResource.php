<?php

namespace App\Domains\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'event_type' => $this->event_type,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
        ];
    }
}
