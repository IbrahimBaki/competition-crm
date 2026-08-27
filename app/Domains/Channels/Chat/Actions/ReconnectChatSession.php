<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Exceptions\ChatSessionNotReconnectableException;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Services\Reconnect\ChatReconnectGuard;

final class ReconnectChatSession
{
    public function handle(ChatSession $session): ChatSession
    {
        $guard = app(ChatReconnectGuard::class);
        if (! $guard->canReconnect($session)) {
            throw new ChatSessionNotReconnectableException;
        }

        $session->update(['disconnected_at' => null, 'last_visitor_seen_at' => now()]);
        $session->events()->create(['type' => 'reconnected']);

        return $session->fresh();
    }
}
