<?php

namespace App\Domains\Knowledge\Services\Retention;

use App\Domains\Knowledge\Models\KnowledgeArticleFeedback;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class ArticleFeedbackPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return KnowledgeArticleFeedback::class;
    }

    public function purge(RetentionPolicy $policy): int
    {
        $cutoffDate = now()->subDays($policy->retentionDays);

        return KnowledgeArticleFeedback::query()
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }
}
