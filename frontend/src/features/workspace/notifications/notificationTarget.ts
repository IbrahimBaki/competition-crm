import type { WorkspaceNotification } from '../types';

// Mirrors app/Domains/Notifications/Enums/NotificationEventType.php. Every
// event type currently falls back to /workspace: the notification payload
// (app/Domains/Notifications/Events/*Notification::payload()) never carries a
// client-usable ticket/task UUID today — e.g. TicketMentionedNotification's
// `ticket_id` is the ticket's internal auto-increment id, not its uuid — so
// there is no reliable deep-link target yet (see .squad/gaps/35-482.md).
// This function is still the single place that maps event type -> route, so
// swapping in a real target only requires editing the matching case here
// once the backend exposes a resolvable id.
const KNOWN_EVENT_TYPES = new Set([
  'sla.warning',
  'sla.breach',
  'ticket.assigned',
  'ticket.transferred',
  'ticket.escalated',
  'ticket.message.posted',
  'user.invited',
  'agent_task.reminder',
  'ticket.mentioned',
  'ticket.watched.update',
  'web_form.submission.acknowledged',
  'ticket.feedback.requested',
]);

export function notificationTarget(notification: WorkspaceNotification): string {
  if (!KNOWN_EVENT_TYPES.has(notification.eventType)) {
    return '/workspace';
  }

  switch (notification.eventType) {
    case 'sla.warning':
    case 'sla.breach':
    case 'ticket.assigned':
    case 'ticket.transferred':
    case 'ticket.escalated':
    case 'ticket.message.posted':
    case 'ticket.mentioned':
    case 'ticket.watched.update':
    case 'ticket.feedback.requested':
    case 'agent_task.reminder':
    case 'user.invited':
    case 'web_form.submission.acknowledged':
    default:
      return '/workspace';
  }
}
