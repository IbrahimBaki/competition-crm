<?php

namespace App\Domains\Integrations\Jobs;

use App\Domains\Integrations\Models\WebhookDelivery;
use App\Domains\Integrations\Services\Webhooks\WebhookSigner;
use App\Domains\Integrations\Services\Webhooks\WebhookTransport;
use App\Support\Http\RequestId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $backoff = [10, 30, 120, 600, 3600];

    public $tries = 6;

    public $timeout = 30;

    public function __construct(private readonly WebhookDelivery $delivery) {}

    public function handle(WebhookTransport $transport): void
    {
        if ($this->delivery->isTerminal()) {
            return;
        }

        $subscription = $this->delivery->subscription;
        if (! $subscription->isActive()) {
            $this->delivery->update(['state' => 'failed']);

            return;
        }

        $timestamp = now()->timestamp;
        $body = json_encode($this->delivery->payload);
        $signature = WebhookSigner::sign($body, $subscription->secret, $timestamp);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $timestamp,
            'X-Webhook-Event' => $this->delivery->event_type,
            'X-Webhook-Delivery' => $this->delivery->uuid,
            'X-Request-Id' => RequestId::current(),
        ];

        $this->delivery->update([
            'state' => 'delivering',
            'attempt_count' => $this->delivery->attempt_count + 1,
        ]);

        $result = $transport->send($headers, $body, $subscription->target_url);

        if ($result->success) {
            $this->delivery->update([
                'state' => 'delivered',
                'delivered_at' => now(),
            ]);
            $subscription->update([
                'last_success_at' => now(),
                'consecutive_failures' => 0,
            ]);
        } else {
            $consecutiveFailures = $subscription->consecutive_failures + 1;

            $this->delivery->update([
                'state' => 'pending',
                'last_status_code' => $result->statusCode,
                'last_error' => $result->error,
                'next_attempt_at' => now()->addSeconds($this->backoff[$this->attempts() - 1] ?? end($this->backoff)),
            ]);

            $subscription->update([
                'last_failure_at' => now(),
                'consecutive_failures' => $consecutiveFailures,
                'disabled_at' => $consecutiveFailures >= config('integrations.webhooks.auto_disable_after_failures', 20)
                    ? now()
                    : null,
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->delivery->update(['state' => 'failed']);
            } else {
                $this->release($this->backoff[$this->attempts() - 1] ?? end($this->backoff));
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook delivery job failed', [
            'delivery_id' => $this->delivery->uuid,
            'error' => $exception->getMessage(),
        ]);
        $this->delivery->update(['state' => 'failed']);
    }
}
