<?php

namespace App\Domains\Ticketing\Models;

enum MessageAuthorType: string
{
    case Customer = 'customer';
    case Agent = 'agent';
    case Ai = 'ai';
    case System = 'system';
}
