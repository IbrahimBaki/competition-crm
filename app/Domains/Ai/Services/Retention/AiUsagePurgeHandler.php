<?php

namespace App\Domains\Ai\Services\Retention;

use App\Domains\Ai\Models\AiUsageRecord;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

final class AiUsagePurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'ai_usage_records';
    }

    public function purge(RetentionPolicy $policy): int
    {
        $retentionDays = config('retention.classes.ai_usage_records.days', 180);
        $cutoff = now()->subDays($retentionDays);

        $count = AiUsageRecord::query()
            ->where('occurred_at', '<', $cutoff)
            ->count();

        AiUsageRecord::query()
            ->where('occurred_at', '<', $cutoff)
            ->delete();

        return $count;
    }
}
