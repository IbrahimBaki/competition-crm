<?php

namespace App\Domains\Integrations\Services\Webhooks;

interface WebhookTransport
{
    public function send(array $headers, string $body, string $url): WebhookSendResult;
}
