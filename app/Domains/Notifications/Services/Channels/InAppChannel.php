<?php

namespace App\Domains\Notifications\Services\Channels;

use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationDeliveryAttempt;

final class InAppChannel implements NotificationChannelTransport
{
    public function send(Notification $notification): void
    {
        // In-app notifications are already persisted in the database
        // Just mark the state as sent
        $notification->update([
            'state' => NotificationState::Sent->value,
            'sent_at' => now(),
        ]);

        NotificationDeliveryAttempt::create([
            'notification_id' => $notification->id,
            'attempt_no' => $notification->attempts + 1,
            'channel' => $notification->channel,
            'outcome' => 'sent',
            'attempted_at' => now(),
        ]);
    }
}
