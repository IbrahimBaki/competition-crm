<?php

namespace App\Domains\Integrations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WebhookSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'target_url' => $this->target_url,
            'event_types' => $this->event_types,
            'is_active' => $this->is_active,
            'last_success_at' => $this->last_success_at?->toIso8601String(),
            'last_failure_at' => $this->last_failure_at?->toIso8601String(),
            'consecutive_failures' => $this->consecutive_failures,
            'disabled_at' => $this->disabled_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
