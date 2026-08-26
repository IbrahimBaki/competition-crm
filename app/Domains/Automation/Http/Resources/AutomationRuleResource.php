<?php

namespace App\Domains\Automation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRuleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'key' => $this->key,
            'name' => $this->name,
            'trigger' => $this->trigger->value,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'stop_on_match' => $this->stop_on_match,
            'conditions' => $this->conditions,
            'actions' => $this->actions,
            'escalation_level' => $this->escalation_level,
            'cooldown_minutes' => $this->cooldown_minutes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
