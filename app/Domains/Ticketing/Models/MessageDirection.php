<?php

namespace App\Domains\Ticketing\Models;

enum MessageDirection: string
{
    case Inbound = 'inbound';
    case Outbound = 'outbound';
}
