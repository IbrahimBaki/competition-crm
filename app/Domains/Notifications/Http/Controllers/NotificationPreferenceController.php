<?php

namespace App\Domains\Notifications\Http\Controllers;

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationEventType;
use App\Domains\Notifications\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', NotificationPreference::class);

        $preferences = [];

        foreach (NotificationEventType::cases() as $eventType) {
            foreach (NotificationChannel::cases() as $channel) {
                $pref = NotificationPreference::query()
                    ->where('user_id', $request->user()->id)
                    ->where('event_type', $eventType->value)
                    ->where('channel', $channel->value)
                    ->first();

                $enabled = $pref ? $pref->enabled : (config('notifications.defaults')[$eventType->value][$channel->value] ?? false);

                $preferences[] = [
                    'event_type' => $eventType->value,
                    'channel' => $channel->value,
                    'enabled' => $enabled,
                ];
            }
        }

        return ApiResponse::success([
            'data' => $preferences,
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        foreach ($request->validated()['preferences'] as $pref) {
            NotificationPreference::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'event_type' => $pref['event_type'],
                    'channel' => $pref['channel'],
                ],
                [
                    'enabled' => $pref['enabled'],
                ],
            );
        }

        return ApiResponse::success([
            'message' => 'Preferences updated successfully',
        ]);
    }
}
