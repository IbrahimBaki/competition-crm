<?php

namespace App\Domains\Automation\Models;

enum NoAgentFallback: string
{
    case LeaveUnassigned = 'leave_unassigned';
    case DepartmentQueue = 'department_queue';
    case Escalate = 'escalate';
}
