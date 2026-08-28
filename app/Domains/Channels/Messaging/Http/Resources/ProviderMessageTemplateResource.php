<?php

namespace App\Domains\Channels\Messaging\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderMessageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'channel' => $this->channel->value,
            'key' => $this->key,
            'provider_template_name' => $this->provider_template_name,
            'body' => [
                'en' => $this->body_en,
                'ar' => $this->body_ar,
            ],
            'variables' => $this->variables ?? [],
            'is_active' => $this->is_active,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
