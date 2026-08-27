<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Services\Transcript\PersistChatTranscript;

final class AbandonChatSession
{
    public function __construct(
        private readonly PersistChatTranscript $persistTranscript,
    ) {}

    public function handle(ChatSession $session): ChatSession
    {
        $session->update(['state' => ChatSessionState::Abandoned->value, 'abandoned_at' => now()]);
        if ($session->messages()->count() > 0) {
            $this->persistTranscript->handle($session);
        }
        $session->events()->create(['type' => 'abandoned']);

        return $session->fresh();
    }
}
