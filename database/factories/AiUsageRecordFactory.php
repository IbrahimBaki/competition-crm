<?php

namespace Database\Factories;

use App\Domains\Ai\Models\AiUsageOutcome;
use App\Domains\Ai\Models\AiUsageRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiUsageRecordFactory extends Factory
{
    protected $model = AiUsageRecord::class;

    public function definition(): array
    {
        return [
            'feature' => 'summary',
            'provider' => 'null',
            'model' => 'null',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'cost_micros' => 0,
            'outcome' => AiUsageOutcome::Success,
            'occurred_at' => now(),
        ];
    }
}
