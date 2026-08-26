<?php

namespace App\Domains\Automation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRuleExecutionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'rule_id' => $this->rule?->uuid,
            'ticket_id' => $this->ticket?->uuid,
            'trigger' => $this->trigger,
            'outcome' => $this->outcome->value,
            'condition_snapshot' => $this->condition_snapshot,
            'changes' => $this->changes,
            'reason' => $this->reason,
            'executed_at' => $this->executed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
