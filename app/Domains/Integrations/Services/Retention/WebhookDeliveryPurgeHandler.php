<?php

namespace App\Domains\Integrations\Services\Retention;

use App\Domains\Integrations\Models\WebhookDelivery;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

final class WebhookDeliveryPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'webhook_deliveries';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        return WebhookDelivery::where('created_at', '<', $policy->cutoff)
            ->where(function ($q) {
                $q->where('state', 'delivered')
                    ->orWhere('state', 'failed');
            })
            ->delete();
    }
}
