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

        // paginated(), not success() — a list response must carry pagination meta.
        return ApiResponse::paginated(
            WebhookDeliveryResource::collection($deliveries),
            $deliveries,
        );
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
