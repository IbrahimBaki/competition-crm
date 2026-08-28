<?php

namespace App\Domains\Channels\Email\Models;

enum InboundState: string
{
    case Received = 'received';
    case Processed = 'processed';
    case Suppressed = 'suppressed';
    case Failed = 'failed';
}
