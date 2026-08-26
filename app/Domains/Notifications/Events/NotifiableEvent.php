<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Notifications\Enums\NotificationEventType;

interface NotifiableEvent
{
    public function eventId(): string;

    public function eventType(): NotificationEventType;

    /** @return array<int, int> user ids */
    public function recipientIds(): array;

    /** @return array<string, scalar|null> */
    public function payload(): array;
}
