<?php

namespace App\Domains\Sla\Models;

enum SlaClockState: string
{
    case Running = 'running';
    case Paused = 'paused';
    case Met = 'met';
    case Breached = 'breached';
    case Cancelled = 'cancelled';
}
