<?php

namespace App\Domains\Workspace\Models;

enum AgentTaskState: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';
}
