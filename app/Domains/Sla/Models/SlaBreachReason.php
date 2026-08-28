<?php

namespace App\Domains\Sla\Models;

enum SlaBreachReason: string
{
    case TargetExceeded = 'target_exceeded';
    case ExceededWhilePaused = 'exceeded_while_paused';
    case ExceededAfterReclassification = 'exceeded_after_reclassification';
}
