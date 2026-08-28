<?php

namespace App\Domains\Sla\Http\Resources;

use App\Domains\Sla\Models\SlaPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

/** @mixin SlaPolicy */
class SlaPolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'branch_id' => (string) $this->branch_id,
            'name' => $this->name->forLocale(App::getLocale()),
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'warning_threshold_percent' => (int) $this->warning_threshold_percent,
            'targets' => SlaTargetResource::collection($this->whenLoaded('targets')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
