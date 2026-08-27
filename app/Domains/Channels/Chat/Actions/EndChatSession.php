<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Services\Transcript\PersistChatTranscript;

final class EndChatSession
{
    public function __construct(
        private readonly PersistChatTranscript $persistTranscript,
    ) {}

    public function handle(ChatSession $session, ?string $reason = null): ChatSession
    {
        $session->update(['state' => ChatSessionState::Ended->value, 'ended_at' => now(), 'end_reason' => $reason]);
        $this->persistTranscript->handle($session);
        $session->events()->create(['type' => 'ended', 'reason' => $reason]);

        return $session->fresh();
    }
}
