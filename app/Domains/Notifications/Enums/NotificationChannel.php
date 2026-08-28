<?php

namespace App\Domains\Notifications\Enums;

enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Mail = 'mail';
    // TODO(story-466-followup): Implement SMS and Push channels
    // case Sms = 'sms';
    // case Push = 'push';
}
