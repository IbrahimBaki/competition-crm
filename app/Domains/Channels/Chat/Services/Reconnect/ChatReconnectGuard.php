<?php

namespace App\Domains\Channels\Chat\Services\Reconnect;

use App\Domains\Channels\Chat\Models\ChatSession;

final class ChatReconnectGuard
{
    public function canReconnect(ChatSession $session): bool
    {
        if ($session->state->isTerminal()) {
            return false;
        }

        $window = (int) config('channels.chat.reconnect_window_seconds', 300);
        $lastActivity = $session->disconnected_at ?? $session->last_visitor_seen_at;

        if (! $lastActivity) {
            return true;
        }

        return $lastActivity->diffInSeconds(now()) <= $window;
    }
}
