<?php

namespace App\Domains\Workspace\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentTaskEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type->value,
            'actor' => fn () => [
                'uuid' => $this->actor?->uuid,
                'name' => $this->actor?->name,
            ],
            'payload' => $this->payload,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
