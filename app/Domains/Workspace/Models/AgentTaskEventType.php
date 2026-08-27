<?php

namespace App\Domains\Workspace\Models;

enum AgentTaskEventType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case StateChanged = 'state_changed';
    case Reassigned = 'reassigned';
    case ReminderScheduled = 'reminder_scheduled';
    case ReminderFired = 'reminder_fired';
}
