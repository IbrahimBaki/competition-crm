<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Models\NotificationPreference;
use App\Models\User;

final class NotificationPreferenceResolver
{
    public function isEnabled(User $user, string $eventType, string $channel): bool
    {
        $preference = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('event_type', $eventType)
            ->where('channel', $channel)
            ->first();

        if ($preference) {
            return $preference->enabled;
        }

        $defaults = config('notifications.defaults');
        if (! isset($defaults[$eventType])) {
            return false;
        }

        return $defaults[$eventType][$channel] ?? false;
    }
}
