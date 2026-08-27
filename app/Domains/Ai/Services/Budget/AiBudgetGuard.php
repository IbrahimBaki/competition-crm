<?php

namespace App\Domains\Ai\Services\Budget;

use App\Domains\Ai\Exceptions\AiBudgetExceededException;
use App\Domains\Ai\Models\AiUsageOutcome;
use App\Domains\Ai\Models\AiUsageRecord;

class AiBudgetGuard
{
    public function check(): void
    {
        $limit = config('ai.budget.limit_micros');
        $blockWhenExceeded = config('ai.budget.block_when_exceeded');
        $period = config('ai.budget.period');

        // Zero limit means unlimited for null provider, only block for real providers
        if ($limit === 0) {
            return;
        }

        if (! $blockWhenExceeded) {
            return;
        }

        $spent = $this->getSpentThisPeriod($period);

        if ($spent >= $limit) {
            AiUsageRecord::query()->create([
                'feature' => 'unknown',
                'provider' => config('ai.provider', 'null'),
                'model' => 'null',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'cost_micros' => 0,
                'outcome' => AiUsageOutcome::BudgetBlocked,
                'occurred_at' => now(),
            ]);

            throw new AiBudgetExceededException('AI budget has been exceeded');
        }
    }

    private function getSpentThisPeriod(string $period): int
    {
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return AiUsageRecord::query()
            ->where('occurred_at', '>=', $startDate)
            ->sum('cost_micros') ?? 0;
    }
}
