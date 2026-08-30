import { describe, it, expect } from 'vitest';
import { notificationTarget } from '../notificationTarget';
import type { WorkspaceNotification } from '../../types';

function notification(eventType: string): WorkspaceNotification {
  return {
    uuid: 'n-1',
    eventType,
    channel: 'in_app',
    state: 'sent',
    subject: null,
    body: null,
    payload: null,
    readAt: null,
    sentAt: null,
    createdAt: '2026-08-01T00:00:00+00:00',
  };
}

const KNOWN_EVENT_TYPES = [
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
];

describe('notificationTarget', () => {
  it.each(KNOWN_EVENT_TYPES)('maps %s to a route', (eventType) => {
    expect(notificationTarget(notification(eventType))).toBe('/workspace');
  });

  it('falls back to /workspace for an unknown event type', () => {
    expect(notificationTarget(notification('something.unheard_of'))).toBe('/workspace');
  });
});
