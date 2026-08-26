<?php

namespace App\Domains\Sla\Models;

enum SlaTargetType: string
{
    case FirstResponse = 'first_response';
    case Resolution = 'resolution';
}
