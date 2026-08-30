import type { WorkspaceNotification } from '../types';

export function mapNotification(raw: unknown): WorkspaceNotification {
  const r = raw as Record<string, unknown>;
  return {
    uuid: String(r.uuid),
    eventType: String(r.event_type ?? ''),
    channel: String(r.channel ?? ''),
    state: String(r.state ?? ''),
    subject: (r.subject as string | null) ?? null,
    body: (r.body as string | null) ?? null,
    payload: (r.payload as Record<string, unknown> | null) ?? null,
    readAt: (r.read_at as string | null) ?? null,
    sentAt: (r.sent_at as string | null) ?? null,
    createdAt: String(r.created_at ?? ''),
  };
}
