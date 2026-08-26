<?php

namespace App\Domains\Notifications\Services\Channels;

use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationDeliveryAttempt;

final class MailChannel implements NotificationChannelTransport
{
    public function send(Notification $notification): void
    {
        // TODO(story-466): Implement actual email sending
        // For now, just mark as sent
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
