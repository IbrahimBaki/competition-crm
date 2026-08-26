<?php

namespace App\Domains\Automation\Models;

enum EscalationTargetType: string
{
    case User = 'user';
    case Role = 'role';
    case Department = 'department';
}
