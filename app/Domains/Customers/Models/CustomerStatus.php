<?php

namespace App\Domains\Customers\Models;

enum CustomerStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Anonymised = 'anonymised';
}
