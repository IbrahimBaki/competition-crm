<?php

namespace App\Domains\Integrations\Services\Webhooks;

readonly class WebhookSendResult
{
    public function __construct(
        public bool $success,
        public ?int $statusCode = null,
        public ?string $error = null,
    ) {}
}
