<?php

namespace App\Domains\Sla\Http\Resources;

use App\Domains\Sla\Models\SlaTarget;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SlaTarget */
class SlaTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'target_type' => $this->target_type?->value,
            'priority' => $this->priority,
            'category_id' => $this->category?->uuid,
            'service_tier' => $this->service_tier,
            'minutes' => (int) $this->minutes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
