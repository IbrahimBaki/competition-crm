<?php

namespace App\Domains\Ai\Services\Retention;

use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

final class AiSuggestionPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'ai_suggestions';
    }

    public function purge(RetentionPolicy $policy): int
    {
        $retentionDays = config('retention.classes.ai_suggestions.days', 90);
        $cutoff = now()->subDays($retentionDays);

        $count = AiSuggestion::query()
            ->where('created_at', '<', $cutoff)
            ->whereNotIn('state', [AiSuggestionState::Pending->value])
            ->count();

        // Never purge pending suggestions (they may be resolved soon)
        AiSuggestion::query()
            ->where('created_at', '<', $cutoff)
            ->whereNotIn('state', [AiSuggestionState::Pending->value])
            ->delete();

        return $count;
    }
}
