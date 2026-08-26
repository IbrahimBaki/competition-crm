<?php

namespace App\Domains\Notifications\Services\Channels;

use App\Domains\Notifications\Models\Notification;

interface NotificationChannelTransport
{
    public function send(Notification $notification): void;
}
