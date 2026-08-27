<?php

namespace App\Domains\Workspace\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'state' => $this->state->value,
            'due_at' => $this->due_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'is_overdue' => $this->computeIsOverdue(),
            'owner' => fn () => [
                'uuid' => $this->owner?->uuid,
                'name' => $this->owner?->name,
            ],
            'ticket' => $this->whenLoaded('ticket', fn () => [
                'uuid' => $this->ticket->uuid,
                'reference' => $this->ticket->reference,
            ]),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    private function computeIsOverdue(): bool
    {
        // Only open or in_progress tasks can be overdue
        if (! in_array($this->state->value, ['open', 'in_progress'])) {
            return false;
        }

        // No due date = not overdue
        if (! $this->due_at) {
            return false;
        }

        // Due date is in the past = overdue
        return $this->due_at < now();
    }
}
