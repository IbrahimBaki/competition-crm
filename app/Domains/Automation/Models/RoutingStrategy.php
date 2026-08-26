<?php

namespace App\Domains\Automation\Models;

enum RoutingStrategy: string
{
    case Manual = 'manual';
    case RoundRobin = 'round_robin';
    case LeastBusy = 'least_busy';
    case SkillBased = 'skill_based';
}
