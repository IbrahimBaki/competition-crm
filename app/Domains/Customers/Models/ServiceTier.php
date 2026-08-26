<?php

namespace App\Domains\Customers\Models;

enum ServiceTier: string
{
    case Standard = 'standard';
    case Priority = 'priority';
    case Vip = 'vip';
}
