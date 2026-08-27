<?php

namespace App\Domains\Integrations\Services\Webhooks;

final class WebhookSigner
{
    public static function sign(string $body, string $secret, int $timestamp): string
    {
        $payload = "{$timestamp}.{$body}";
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }
}
