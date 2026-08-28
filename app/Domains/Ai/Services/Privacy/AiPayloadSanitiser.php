<?php

namespace App\Domains\Ai\Services\Privacy;

use Psr\Log\LoggerInterface;

class AiPayloadSanitiser
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function sanitise(string $text): string
    {
        if (! config('ai.privacy.redact_personal_data')) {
            $this->logger->warning('AI redaction is disabled - personal data will be sent to provider', context: ['feature' => 'ai.privacy']);

            return $text;
        }

        // Email addresses
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/u', '[email]', $text);

        // Phone numbers (various formats)
        $text = preg_replace('/(\+\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/u', '[phone]', $text);

        // National/ID numbers (9-12 digit sequences)
        $text = preg_replace('/\b\d{9,12}\b/u', '[id]', $text);

        // API tokens/keys (common patterns)
        $text = preg_replace('/(["\']?)(api[_-]?key|token|auth|secret)["\']?\s*[:=]\s*["\']?[\w\-_.]+["\']?/i', '$1$2$1:[redacted]', $text);

        // Arabic content preservation - only redact sensitive patterns, keep text
        return $text;
    }

    public function sanitiseArray(array $segments): array
    {
        return array_map(fn (string $segment) => $this->sanitise($segment), $segments);
    }
}
