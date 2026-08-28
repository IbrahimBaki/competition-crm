<?php

namespace App\Domains\Channels\Chat\Models;

enum ChatAvailabilityOutcome: string
{
    case Connect = 'connect';
    case Queue = 'queue';
    case OfflineForm = 'offline_form';
    case QueueOrOfflineForm = 'queue_or_offline_form';
}
