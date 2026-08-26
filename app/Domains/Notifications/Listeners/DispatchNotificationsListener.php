<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\Events\NotifiableEvent;
use App\Domains\Notifications\Services\NotificationDispatcher;

final class DispatchNotificationsListener
{
    public function __construct(
        private NotificationDispatcher $dispatcher,
    ) {}

    public function handle(NotifiableEvent $event): void
    {
        $this->dispatcher->handle($event);
    }
}
