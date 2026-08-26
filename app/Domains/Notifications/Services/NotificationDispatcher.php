<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Events\NotifiableEvent;
use App\Domains\Notifications\Jobs\DeliverNotification;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Services\Channels\NotificationChannelRegistry;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

final class NotificationDispatcher
{
    public function __construct(
        private NotificationPreferenceResolver $preferenceResolver,
        private NotificationPayloadAuthoriser $payloadAuthoriser,
        private TemplateRenderer $templateRenderer,
        private NotificationChannelRegistry $channelRegistry,
    ) {}

    public function handle(NotifiableEvent $event): void
    {
        $recipients = User::query()
            ->whereIn('id', $event->recipientIds())
            ->where('anonymised_at', null)
            ->where('deactivated_at', null)
            ->get();

        foreach ($recipients as $recipient) {
            foreach ([NotificationChannel::InApp, NotificationChannel::Mail] as $channel) {
                $this->dispatchForChannel($recipient, $event, $channel);
            }
        }
    }

    private function dispatchForChannel(
        User $recipient,
        NotifiableEvent $event,
        NotificationChannel $channel,
    ): void {
        $eventType = $event->eventType();
        $channelValue = $channel->value;

        if (! $this->preferenceResolver->isEnabled($recipient, $eventType->value, $channelValue)) {
            Notification::query()->firstOrCreate(
                [
                    'event_id' => $event->eventId(),
                    'recipient_user_id' => $recipient->id,
                    'channel' => $channelValue,
                ],
                [
                    'uuid' => Str::uuid(),
                    'event_type' => $eventType->value,
                    'state' => NotificationState::Suppressed->value,
                    'locale' => $recipient->locale ?? config('app.locale'),
                    'payload' => $event->payload(),
                    'failure_code' => 'notification.preference_disabled',
                ],
            );

            return;
        }

        $payload = $this->payloadAuthoriser->authorizeAndFilter($recipient, $eventType, $event->payload());

        if ($payload === null) {
            Notification::query()->firstOrCreate(
                [
                    'event_id' => $event->eventId(),
                    'recipient_user_id' => $recipient->id,
                    'channel' => $channelValue,
                ],
                [
                    'uuid' => Str::uuid(),
                    'event_type' => $eventType->value,
                    'state' => NotificationState::Suppressed->value,
                    'locale' => $recipient->locale ?? config('app.locale'),
                    'payload' => $event->payload(),
                    'failure_code' => 'notification.not_authorised',
                ],
            );

            return;
        }

        $locale = $recipient->locale ?? config('app.locale');

        try {
            $rendered = $this->templateRenderer->render($eventType, $channelValue, $locale, $payload);
        } catch (\Exception $e) {
            Notification::query()->firstOrCreate(
                [
                    'event_id' => $event->eventId(),
                    'recipient_user_id' => $recipient->id,
                    'channel' => $channelValue,
                ],
                [
                    'uuid' => Str::uuid(),
                    'event_type' => $eventType->value,
                    'state' => NotificationState::Failed->value,
                    'locale' => $locale,
                    'payload' => $payload,
                    'failure_code' => 'notification.template_missing',
                    'failure_reason' => $e->getMessage(),
                ],
            );

            return;
        }

        $notification = Notification::query()->firstOrCreate(
            [
                'event_id' => $event->eventId(),
                'recipient_user_id' => $recipient->id,
                'channel' => $channelValue,
            ],
            [
                'uuid' => Str::uuid(),
                'event_type' => $eventType->value,
                'state' => NotificationState::Pending->value,
                'locale' => $locale,
                'payload' => $payload,
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
            ],
        );

        if ($notification->wasRecentlyCreated && $notification->state === NotificationState::Pending->value) {
            Queue::onConnection(config('notifications.queue'))->push(
                new DeliverNotification($notification->uuid)
            );
        }
    }
}
