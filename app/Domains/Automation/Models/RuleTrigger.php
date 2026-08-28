<?php

namespace App\Domains\Automation\Models;

enum RuleTrigger: string
{
    case TicketCreated = 'ticket_created';
    case TicketUpdated = 'ticket_updated';
    case StatusChanged = 'status_changed';
    case PriorityChanged = 'priority_changed';
    case DepartmentTransferred = 'department_transferred';
    case MessagePosted = 'message_posted';
    case SlaWarningRaised = 'sla_warning_raised';
    case SlaBreached = 'sla_breached';
    case Scheduled = 'scheduled';
    case ManualEscalation = 'manual_escalation';
}
