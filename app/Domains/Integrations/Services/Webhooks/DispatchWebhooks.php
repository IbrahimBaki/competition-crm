<?php

namespace App\Domains\Integrations\Services\Webhooks;

use App\Domains\Integrations\Jobs\DeliverWebhookJob;
use App\Domains\Integrations\Models\WebhookDelivery;
use App\Domains\Integrations\Models\WebhookSubscription;
use App\Support\Http\RequestId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class DispatchWebhooks
{
    public function __construct() {}

    public function dispatch(string $eventType, string $eventUuid, array $payload): void
    {
        if (! config('integrations.webhooks.enabled', false)) {
            return;
        }

        try {
            DB::afterCommit(function () use ($eventType, $eventUuid, $payload) {
                $subscriptions = WebhookSubscription::where('is_active', true)
                    ->whereNull('disabled_at')
                    ->get();

                foreach ($subscriptions as $subscription) {
                    if (! $subscription->matchesEventType($eventType)) {
                        continue;
                    }

                    // Use firstOrCreate for idempotent delivery creation
                    $delivery = WebhookDelivery::firstOrCreate(
                        [
                            'webhook_subscription_id' => $subscription->id,
                            'event_uuid' => $eventUuid,
                        ],
                        [
                            'uuid' => (string) Str::uuid(),
                            'event_type' => $eventType,
                            'payload' => $payload,
                            'state' => 'pending',
                        ]
                    );

                    // Dispatch delivery job
                    DeliverWebhookJob::dispatch($delivery);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Webhook dispatch failed', [
                'event_type' => $eventType,
                'event_uuid' => $eventUuid,
                'error' => $e->getMessage(),
                'request_id' => RequestId::current(),
            ]);
            // Never throw - webhook failures must not affect ticket writes
        }
    }
}
