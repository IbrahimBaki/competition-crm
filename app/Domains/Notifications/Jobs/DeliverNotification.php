<?php

namespace App\Domains\Notifications\Jobs;

use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationDeliveryAttempt;
use App\Domains\Notifications\Services\Channels\NotificationChannelRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class DeliverNotification implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        private string $notificationUuid,
    ) {
        $this->onQueue(config('notifications.queue'));
    }

    public function backoff(): array
    {
        return [10, 60, 300, 900];
    }

    public function uniqueId(): string
    {
        return $this->notificationUuid;
    }

    public function handle(NotificationChannelRegistry $channelRegistry): void
    {
        $notification = Notification::query()
            ->where('uuid', $this->notificationUuid)
            ->first();

        if (! $notification) {
            return;
        }

        if ($notification->state === NotificationState::Sent->value) {
            return;
        }

        $transport = $channelRegistry->get($notification->channel);
        if (! $transport) {
            $this->fail(new \Exception("Unknown notification channel: {$notification->channel}"));

            return;
        }

        try {
            $transport->send($notification);
        } catch (Throwable $e) {
            $this->recordAttempt($notification, 'retryable_failure', $e->getCode(), $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $notification = Notification::query()
            ->where('uuid', $this->notificationUuid)
            ->first();

        if (! $notification) {
            return;
        }

        $notification->update([
            'state' => NotificationState::Failed->value,
            'failure_code' => 'notification.delivery_failed',
            'failure_reason' => substr($e->getMessage(), 0, 500),
            'attempts' => $notification->attempts + 1,
        ]);

        $this->recordAttempt($notification, 'permanent_failure', (string) $e->getCode(), substr($e->getMessage(), 0, 500));
    }

    private function recordAttempt(
        Notification $notification,
        string $outcome,
        ?string $errorCode = null,
        ?string $errorMessage = null,
    ): void {
        NotificationDeliveryAttempt::create([
            'notification_id' => $notification->id,
            'attempt_no' => $notification->attempts + 1,
            'channel' => $notification->channel,
            'outcome' => $outcome,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'attempted_at' => now(),
        ]);
    }
}
