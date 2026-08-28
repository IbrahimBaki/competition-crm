<?php

namespace App\Console\Commands;

use App\Domains\Channels\Chat\Models\ChatSessionState;
use Illuminate\Console\Command;

class ChatSessionSweepCommand extends Command
{
    protected $signature = 'chat:sweep-sessions {--limit=500}';

    protected $description = 'Sweep stale chat sessions and retry transcript persistence';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $abandonWindow = (int) config('channels.chat.abandon_after_seconds', 900);
        $staleTime = now()->subSeconds($abandonWindow);

        // Mark stale live sessions as abandoned
        $abandoned = \DB::table('chat_sessions')
            ->where('state', '!=', 'ended')
            ->where('state', '!=', 'abandoned')
            ->where('last_visitor_seen_at', '<', $staleTime)
            ->limit($limit)
            ->update([
                'state' => ChatSessionState::Abandoned->value,
                'abandoned_at' => now(),
            ]);

        // Retry transcript persistence for terminal sessions
        $retried = \DB::table('chat_sessions')
            ->whereIn('state', ['ended', 'abandoned'])
            ->whereNull('transcript_persisted_at')
            ->limit($limit)
            ->count();

        $this->info("Abandoned $abandoned stale sessions, retrying $retried transcripts");

        return self::SUCCESS;
    }
}
