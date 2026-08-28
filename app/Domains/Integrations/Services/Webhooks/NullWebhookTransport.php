<?php

namespace App\Domains\Integrations\Services\Webhooks;

final class NullWebhookTransport implements WebhookTransport
{
    public function send(array $headers, string $body, string $url): WebhookSendResult
    {
        return new WebhookSendResult(success: true, statusCode: 200);
    }
}
