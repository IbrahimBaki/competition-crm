<?php

namespace App\Domains\Ai\Services\Usage;

use App\Domains\Ai\Models\AiUsageOutcome;
use App\Domains\Ai\Models\AiUsageRecord;
use Illuminate\Support\Facades\DB;

class RecordAiUsage
{
    public function record(
        string $feature,
        string $provider,
        string $model,
        int $promptTokens,
        int $completionTokens,
        int $costMicros,
        AiUsageOutcome $outcome,
        ?int $ticketId = null,
        ?int $userId = null,
    ): void {
        // Record outside the caller's transaction to ensure cost is preserved even if business write rolls back
        DB::connection()->getPdo()->beginTransaction();

        try {
            AiUsageRecord::query()->create([
                'feature' => $feature,
                'provider' => $provider,
                'model' => $model,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'cost_micros' => $costMicros,
                'outcome' => $outcome,
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'occurred_at' => now(),
            ]);

            DB::connection()->getPdo()->commit();
        } catch (\Exception $e) {
            DB::connection()->getPdo()->rollBack();
            throw $e;
        }
    }
}
