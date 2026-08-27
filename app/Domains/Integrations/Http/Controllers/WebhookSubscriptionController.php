<?php

namespace App\Domains\Integrations\Http\Controllers;

use App\Domains\Integrations\Http\Resources\WebhookSubscriptionResource;
use App\Domains\Integrations\Models\WebhookSubscription;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class WebhookSubscriptionController
{
    public function index(Request $request)
    {
        $subscriptions = WebhookSubscription::paginate();
        return ApiResponse::success(WebhookSubscriptionResource::collection($subscriptions));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'required|url|starts_with:https://|max:2048',
            'event_types' => 'required|array|min:1',
            'event_types.*' => 'string',
        ]);

        $subscription = WebhookSubscription::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'target_url' => $validated['target_url'],
            'secret' => Str::random(32),
            'event_types' => $validated['event_types'],
            'created_by_user_id' => auth()->id(),
        ]);

        return ApiResponse::success(new WebhookSubscriptionResource($subscription), status: 201);
    }

    public function update(Request $request, WebhookSubscription $subscription)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'target_url' => 'url|starts_with:https://|max:2048',
            'event_types' => 'array|min:1',
            'is_active' => 'boolean',
        ]);

        $subscription->update($validated);
        return ApiResponse::success(new WebhookSubscriptionResource($subscription));
    }

    public function destroy(WebhookSubscription $subscription)
    {
        $subscription->delete();
        return ApiResponse::success(null, status: 204);
    }
}
