<?php

namespace App\Domains\Channels\Chat\Services\Queue;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Models\User;

final class ChatQueue
{
    public function length($departmentId): int
    {
        return ChatSession::where('department_id', $departmentId)
            ->where('state', ChatSessionState::Queued->value)
            ->count();
    }

    public function positionOf(ChatSession $session): int
    {
        return ChatSession::where('department_id', $session->department_id)
            ->where('state', ChatSessionState::Queued->value)
            ->where(fn ($q) => $q->where('queued_at', '<', $session->queued_at)
                ->orWhere(fn ($q2) => $q2->where('queued_at', $session->queued_at)->where('id', '<', $session->id)))
            ->count() + 1;
    }

    public function nextFor(User $agent): ?ChatSession
    {
        return ChatSession::query()
            ->whereIn('department_id', $agent->departments()->pluck('departments.id'))
            ->where('state', ChatSessionState::Queued->value)
            ->orderBy('queued_at', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->first();
    }
}
