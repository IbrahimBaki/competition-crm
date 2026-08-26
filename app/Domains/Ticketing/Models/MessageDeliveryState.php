<?php

namespace App\Domains\Ticketing\Models;

enum MessageDeliveryState: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return $this === self::Read || $this === self::Failed;
    }
}
