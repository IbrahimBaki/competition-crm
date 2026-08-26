<?php

namespace App\Domains\Notifications\Services\Retention;

use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Models\Notification;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

final class NotificationPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'notifications';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        return Notification::query()
            ->where('created_at', '<', $policy->cutoff)
            ->whereIn('state', [NotificationState::Sent->value, NotificationState::Suppressed->value])
            ->delete();
    }
}
