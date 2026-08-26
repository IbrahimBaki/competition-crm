<?php

namespace App\Domains\Notifications\Enums;

enum NotificationState: string
{
    case Pending = 'pending';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Suppressed = 'suppressed';
}
