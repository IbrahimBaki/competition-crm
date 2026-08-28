<?php

namespace App\Domains\Ai\Services\Gate;

use App\Domains\Ai\Exceptions\AiFeatureDisabledException;
use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Models\AiUsageOutcome;
use App\Domains\Ai\Models\AiUsageRecord;

class AiFeatureGate
{
    public function check(AiFeature $feature): void
    {
        if (! config('ai.enabled')) {
            $this->recordUsage($feature, AiUsageOutcome::FeatureDisabled);
            throw new AiFeatureDisabledException('AI is disabled');
        }

        $featureKey = $feature->configKey();
        if (! config("ai.features.{$featureKey}")) {
            $this->recordUsage($feature, AiUsageOutcome::FeatureDisabled);
            throw new AiFeatureDisabledException("Feature {$featureKey} is disabled");
        }
    }

    private function recordUsage(AiFeature $feature, AiUsageOutcome $outcome): void
    {
        AiUsageRecord::query()->create([
            'feature' => $feature->value,
            'provider' => config('ai.provider', 'null'),
            'model' => 'null',
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'cost_micros' => 0,
            'outcome' => $outcome,
            'occurred_at' => now(),
        ]);
    }
}
