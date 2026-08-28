<?php

namespace App\Domains\Channels\Chat\Services\Retention;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class ChatSessionPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return ChatSession::class;
    }

    public function purge(RetentionPolicy $policy): int
    {
        $days = (int) config('channels.chat.transcript_retention_days', 90);
        $cutoff = now()->subDays($days);

        return \DB::table('chat_sessions')
            ->where('created_at', '<', $cutoff)
            ->whereNotNull('transcript_persisted_at')
            ->delete();
    }
}
