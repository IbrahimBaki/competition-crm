// Hand-written domain types mirroring the backend JsonResource shapes verbatim,
// following the pattern in frontend/src/features/tickets/types.ts. Keep in sync
// with:
//   app/Domains/Workspace/Http/Resources/AgentTaskResource.php
//   app/Domains/Workspace/Http/Resources/QuickReplyResource.php
//   app/Domains/Notifications/Http/Resources/NotificationResource.php (untyped —
//     see NotificationResource.php's toArray for the exact keys used below)
//
// Several corrections vs. the story plan's draft types, verified live against
// the running backend (see .squad/gaps/35-482.md for the full investigation):
//   - AgentTaskState is 'open'|'in_progress'|'done'|'cancelled' (NOT 'completed').
//     The generated PostAgentTaskStateBodyState enum ('pending'/'completed') does
//     not match the backend's real AgentTaskState enum — do not use it.
//   - quick-replies/render's real response is { en, ar }, not { output }.

import type { BilingualValue } from '../tickets/utils/bilingual';

export type AgentTaskState = 'open' | 'in_progress' | 'done' | 'cancelled';

export interface AgentTask {
  uuid: string;
  title: string;
  description: string | null;
  state: AgentTaskState;
  dueAt: string | null; // ISO-8601 with offset, e.g. 2026-08-27T08:42:20+00:00
  completedAt: string | null;
  cancelledAt: string | null;
  isOverdue: boolean; // server-computed; never recompute
  owner: { uuid: string | null; name: string | null };
  ticket: { uuid: string; reference: string } | null; // absent unless included
  createdAt: string;
  updatedAt: string;
}

export interface QuickReplySummary {
  uuid: string;
  scope: 'personal' | 'shared';
  title: BilingualValue;
  isActive: boolean;
}

export interface RenderedQuickReply {
  en: string;
  ar: string;
}

export interface WorkspaceNotification {
  uuid: string;
  eventType: string;
  channel: string;
  state: string;
  subject: string | null;
  body: string | null;
  // Raw event payload (app/Domains/Notifications/Events/*Notification::payload()).
  // Keys vary per event type and, as verified live, never carry a client-usable
  // UUID today (e.g. TicketMentionedNotification's `ticket_id` is the ticket's
  // internal auto-increment id, not its uuid) — see .squad/gaps/35-482.md.
  // Kept typed as unknown; do not assume any specific key exists.
  payload: Record<string, unknown> | null;
  readAt: string | null;
  sentAt: string | null;
  createdAt: string;
}
