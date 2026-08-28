<?php

namespace App\Domains\Workspace\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuickReplyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'scope' => $this->scope->value,
            'title' => $this->title,
            'body' => $this->body,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
