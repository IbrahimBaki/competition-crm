<?php

namespace App\Domains\Integrations\Http\Controllers;

use App\Domains\Integrations\Http\Resources\WebhookDeliveryResource;
use App\Domains\Integrations\Jobs\DeliverWebhookJob;
use App\Domains\Integrations\Models\WebhookDelivery;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;

final class WebhookDeliveryController
{
    public function index(Request $request)
    {
        $deliveries = WebhookDelivery::paginate();
        return ApiResponse::success(WebhookDeliveryResource::collection($deliveries));
    }

    public function show(WebhookDelivery $delivery)
    {
        return ApiResponse::success(new WebhookDeliveryResource($delivery));
    }

    public function replay(WebhookDelivery $delivery)
    {
        if ($delivery->isTerminal()) {
            $delivery->update(['state' => 'pending', 'attempt_count' => 0, 'next_attempt_at' => now()]);
            DeliverWebhookJob::dispatch($delivery);
        }
        return ApiResponse::success(new WebhookDeliveryResource($delivery));
    }
}
