<?php

namespace App\Domains\Notifications\Enums;

enum NotificationEventType: string
{
    case SlaWarning = 'sla.warning';
    case SlaBreach = 'sla.breach';
    case TicketAssigned = 'ticket.assigned';
    case TicketTransferred = 'ticket.transferred';
    case TicketEscalated = 'ticket.escalated';
    case TicketMessagePosted = 'ticket.message.posted';
    case UserInvited = 'user.invited';
    case AgentTaskReminder = 'agent_task.reminder';
    case TicketMentioned = 'ticket.mentioned';
    case TicketWatchedUpdate = 'ticket.watched.update';
}
