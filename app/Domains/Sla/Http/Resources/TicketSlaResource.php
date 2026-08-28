<?php

namespace App\Domains\Sla\Http\Resources;

use App\Domains\Sla\Models\SlaPosition;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketSlaResource extends JsonResource
{
    public function __construct(
        private readonly ?SlaPosition $position = null,
    ) {
        parent::__construct(null);
    }

    public function toArray($request): ?array
    {
        if (! $this->position) {
            return null;
        }

        return [
            'target_type' => $this->position->targetType->value,
            'state' => $this->position->state->value,
            'due_at' => $this->position->dueAt?->toIso8601String(),
            'target_minutes' => $this->position->targetMinutes,
            'elapsed_minutes' => $this->position->elapsedMinutes,
            'remaining_minutes' => $this->position->remainingMinutes,
            'warning_fired' => $this->position->warningFired,
            'paused_at' => $this->position->pausedAt?->toIso8601String(),
        ];
    }
}
