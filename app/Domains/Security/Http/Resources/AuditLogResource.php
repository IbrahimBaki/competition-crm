<?php

namespace App\Domains\Security\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'actor_uuid' => $this->actor_uuid,
            'action' => $this->action,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'before' => $this->before,
            'after' => $this->after,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
