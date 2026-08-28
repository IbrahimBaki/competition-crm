<?php

namespace App\Domains\Ai\Services;

use App\Domains\Ai\Exceptions\AiProviderUnavailableException;
use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Models\AiUsageOutcome;
use App\Domains\Ai\Services\Budget\AiBudgetGuard;
use App\Domains\Ai\Services\Gate\AiFeatureGate;
use App\Domains\Ai\Services\Privacy\AiPayloadSanitiser;
use App\Domains\Ai\Services\Provider\AiCompletionRequest;
use App\Domains\Ai\Services\Provider\AiCompletionResult;
use App\Domains\Ai\Services\Provider\AiProvider;
use App\Domains\Ai\Services\Usage\RecordAiUsage;
use Psr\Log\LoggerInterface;
use Throwable;

class AiClient
{
    public function __construct(
        private readonly AiFeatureGate $featureGate,
        private readonly AiBudgetGuard $budgetGuard,
        private readonly AiPayloadSanitiser $sanitiser,
        private readonly AiProvider $provider,
        private readonly RecordAiUsage $usageRecorder,
        private readonly LoggerInterface $logger,
    ) {}

    public function complete(
        AiFeature $feature,
        array $promptSegments,
        int $maxTokens = 1000,
        string $locale = 'en',
    ): AiCompletionResult {
        $this->featureGate->check($feature);
        $this->budgetGuard->check();

        $sanitisedSegments = $this->sanitiser->sanitiseArray($promptSegments);
        $request = new AiCompletionRequest(
            featureKey: $feature->configKey(),
            sanitisedPromptSegments: $sanitisedSegments,
            maxTokens: $maxTokens,
            locale: $locale,
        );

        try {
            $result = $this->provider->complete($request);
            $this->usageRecorder->record(
                $feature->value,
                config('ai.provider', 'null'),
                $result->model,
                $result->promptTokens,
                $result->completionTokens,
                $result->costMicros,
                AiUsageOutcome::Success,
            );

            return $result;
        } catch (Throwable $e) {
            $this->logger->error('AI provider error', [
                'feature' => $feature->value,
                'error' => $e->getMessage(),
            ]);

            $this->usageRecorder->record(
                $feature->value,
                config('ai.provider', 'null'),
                'unknown',
                0,
                0,
                0,
                AiUsageOutcome::ProviderUnavailable,
            );

            throw new AiProviderUnavailableException(
                reason: $e->getMessage(),
                detail: 'The AI provider encountered an error',
            );
        }
    }
}
