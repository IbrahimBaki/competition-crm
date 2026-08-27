<?php

namespace App\Domains\Ai\Services\Provider;

final class NullAiProvider implements AiProvider
{
    public function complete(AiCompletionRequest $request): AiCompletionResult
    {
        return new AiCompletionResult(
            content: '[This is a deterministic null provider response for testing]',
            confidence: 0.8,
            model: 'null',
            promptTokens: 10,
            completionTokens: 5,
            costMicros: 0,
        );
    }
}
