<?php

namespace App\Domains\Ai\Services\Provider;

final readonly class AiCompletionResult
{
    public function __construct(
        public string $content,
        public ?float $confidence,
        public string $model,
        public int $promptTokens,
        public int $completionTokens,
        public int $costMicros,
    ) {}
}
