<?php

namespace App\Domains\Channels\Chat\Services\Lifecycle;

use App\Domains\Channels\Chat\Models\ChatSessionState;

final class ChatSessionStateMachine
{
    private const TRANSITIONS = [
        'requested' => ['queued', 'active', 'abandoned'],
        'queued' => ['active', 'abandoned', 'ended'],
        'active' => ['transferred', 'ended', 'abandoned'],
        'transferred' => ['active', 'ended', 'abandoned'],
        'ended' => [],
        'abandoned' => [],
    ];

    public function allowedFrom(ChatSessionState $from): array
    {
        $targets = self::TRANSITIONS[$from->value] ?? [];

        return array_map(fn ($v) => ChatSessionState::from($v), $targets);
    }

    public function allows(ChatSessionState $from, ChatSessionState $to): bool
    {
        if ($from->value === $to->value) {
            return true;
        }

        $targets = self::TRANSITIONS[$from->value] ?? [];

        return in_array($to->value, $targets, true);
    }
}
