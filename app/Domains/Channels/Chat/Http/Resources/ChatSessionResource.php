<?php

namespace App\Domains\Channels\Chat\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'state' => $this->state->value,
            'handled_by' => $this->handled_by,
            'subject' => $this->subject,
            'ticket_id' => $this->ticket?->uuid,
            // Branch and Department have no separate uuid column — unlike
            // Ticket/User/ChatSession, their `id` column is itself a UUID.
            'branch_id' => $this->branch?->id,
            'department_id' => $this->department?->id,
            'assigned_user_id' => $this->assignee?->uuid,
            'visitor' => $this->whenLoaded('visitor', fn () => [
                'display_name' => $this->visitor->display_name,
                'email' => $this->visitor->email,
                'phone' => $this->visitor->phone,
            ]),
            'queue_position' => $this->queue_position,
            'end_reason' => $this->end_reason,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'queued_at' => $this->queued_at?->toIso8601String(),
            'activated_at' => $this->activated_at?->toIso8601String(),
            'transferred_at' => $this->transferred_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'abandoned_at' => $this->abandoned_at?->toIso8601String(),
            'last_visitor_seen_at' => $this->last_visitor_seen_at?->toIso8601String(),
            'last_agent_seen_at' => $this->last_agent_seen_at?->toIso8601String(),
        ];
    }
}
