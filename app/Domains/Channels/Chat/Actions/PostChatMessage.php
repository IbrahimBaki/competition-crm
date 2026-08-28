<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Exceptions\ChatSessionAlreadyEndedException;
use App\Domains\Channels\Chat\Models\ChatMessage;
use App\Domains\Channels\Chat\Models\ChatSession;

final class PostChatMessage
{
    public function handle(ChatSession $session, string $author, string $body, ?string $clientMessageId = null): ChatMessage
    {
        if ($session->state->isTerminal()) {
            throw new ChatSessionAlreadyEndedException;
        }

        // mb_strlen, not strlen: the limit is a character count, and Arabic is
        // two bytes per character — strlen halved the effective limit.
        $maxLen = (int) config('channels.chat.max_message_length', 4000);
        if (mb_strlen($body) > $maxLen) {
            throw new \InvalidArgumentException('Message exceeds max length');
        }

        if ($clientMessageId) {
            $existing = $session->messages()->where('client_message_id', $clientMessageId)->first();
            if ($existing) {
                return $existing;
            }
        }

        $sequence = $session->messages()->max('sequence') + 1;
        $msg = $session->messages()->create([
            'sequence' => $sequence,
            'author_type' => $author,
            'body' => $body,
            'sent_at' => now(),
            'client_message_id' => $clientMessageId,
        ]);

        if ($author === 'visitor') {
            $session->update(['last_visitor_seen_at' => now()]);
        } else {
            $session->update(['last_agent_seen_at' => now()]);
        }

        return $msg;
    }
}
