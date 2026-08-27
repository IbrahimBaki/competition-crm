<?php

namespace App\Domains\Ai\Services\Provider;

final readonly class AiCompletionRequest
{
    public function __construct(
        public string $featureKey,
        public array $sanitisedPromptSegments,
        public int $maxTokens,
        public string $locale,
    ) {}
}
