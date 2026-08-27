<?php

namespace App\Domains\Channels\Email\Services\Parsing;

final readonly class ParsedEmail
{
    /** @param list<string> $referenceIds */
    public function __construct(
        public ?string $messageId,
        public ?string $inReplyTo,
        public array $referenceIds,
        public string $fromAddress,
        public ?string $fromName,
        public ?string $toAddress,
        public ?string $subject,
        public string $textBody,
        public ?string $htmlBody,
        /** @var array<string,string> */
        public array $headers,
    ) {}
}
