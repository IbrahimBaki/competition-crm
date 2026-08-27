<?php

namespace App\Domains\Integrations\Services\Webhooks;

use Illuminate\Support\Facades\Http;

final class HttpWebhookTransport implements WebhookTransport
{
    public function send(array $headers, string $body, string $url): WebhookSendResult
    {
        try {
            $response = Http::timeout(config('integrations.webhooks.timeout_seconds', 5))
                ->withHeaders($headers)
                ->post($url, json_decode($body, true));

            return new WebhookSendResult(
                success: $response->successful(),
                statusCode: $response->status(),
                error: $response->successful() ? null : $response->body(),
            );
        } catch (\Throwable $e) {
            return new WebhookSendResult(
                success: false,
                error: $e->getMessage(),
            );
        }
    }
}
