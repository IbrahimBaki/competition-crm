// Local response types mirroring the backend JsonResource shapes verbatim.
// The orval-generated model types for these endpoints are untyped placeholders
// (e.g. `CollectionResponseDataItem = { [key: string]: unknown }`) because the
// OpenAPI spec declares generic schemas here. These types fill that gap.
// Keep in sync with:
//   app/Domains/Ticketing/Http/Resources/TicketResource.php
//   app/Domains/Ticketing/Http/Resources/TicketMessageResource.php
//   app/Domains/Ticketing/Http/Resources/TicketEventResource.php
//   app/Domains/Ticketing/Http/Resources/TicketLinkResource.php
//   app/Domains/Ticketing/Http/Resources/TicketCategoryResource.php
//   app/Support/Attachments/Resources/AttachmentResource.php

import type { BilingualValue } from './utils/bilingual';

export type TicketPriority = 'low' | 'normal' | 'high' | 'urgent';

export type TicketLifecycleType = 'new' | 'open' | 'pending' | 'resolved' | 'closed' | 'spam';

export interface TicketAvailableTransition {
  uuid: string;
  key: string;
  name: BilingualValue;
  lifecycle_type: TicketLifecycleType;
  requires_reason: boolean;
}

export interface TicketStatusSummary {
  uuid: string;
  key: string;
  name: BilingualValue;
  lifecycle_type: TicketLifecycleType;
  stops_sla_clock: boolean;
}

export type SlaClockState = 'running' | 'paused' | 'met' | 'breached' | 'cancelled';

// Mirrors app/Domains/Sla/Http/Resources/TicketSlaResource.php exactly.
// elapsed_minutes/remaining_minutes are server-computed — never recompute
// them from due_at client-side (see app/Domains/Sla/Services/SlaClockService.php).
export interface TicketSlaPosition {
  target_type: string;
  state: SlaClockState;
  due_at: string | null;
  target_minutes: number;
  elapsed_minutes: number;
  remaining_minutes: number;
  warning_fired: boolean;
  paused_at: string | null;
}

export interface TicketSlaBlock {
  first_response: TicketSlaPosition | null;
  resolution: TicketSlaPosition | null;
}

export interface TicketListItem {
  id: string;
  reference: string;
  customer_id: string | null;
  department_id: string | null;
  category_id: string | null;
  assignee_id: string | null;
  subject: string;
  body: string;
  status: TicketStatusSummary | null;
  priority: TicketPriority | null;
  custom_fields: Record<string, unknown> | null;
  available_transitions: TicketAvailableTransition[];
  merged_into_id: string | null;
  parent_ticket_id: string | null;
  version: number;
  assigned_at: string | null;
  reopen_deadline_at: string | null;
  reopened_count: number;
  is_watched: boolean;
  watcher_count?: number;
  created_at: string | null;
  updated_at: string | null;
  sla: TicketSlaBlock | null;
}

export type TicketDetail = TicketListItem;

/**
 * The list/queue endpoints (TicketController::index, TicketQueueController::mine
 * and ::department) do NOT wrap results in TicketResource — they serialize the
 * raw Eloquent Ticket model via ApiResponse::collection($paginator->items()).
 * This is a backend defect (see PR description) but it is what those three
 * endpoints actually return today, so this is the shape TicketListTable must
 * consume. Notably: `id` here is the ticket's internal auto-increment integer
 * PK, NOT a usable identifier — always use `uuid`. `customer_id`,
 * `ticket_category_id` and `assigned_user_id` are likewise raw internal
 * integer foreign keys with no client-resolvable UUID, so they cannot be
 * rendered as names. `department_id` is the exception: departments use a
 * UUID primary key, so it can be resolved via the departments list.
 */
export interface TicketListRow {
  id: number;
  uuid: string;
  reference: string;
  customer_id: number | null;
  department_id: string | null;
  ticket_category_id: number | null;
  assigned_user_id: number | null;
  subject: string;
  status: TicketLifecycleType;
  priority: TicketPriority;
  custom_fields: Record<string, unknown> | null;
  version: number;
  created_at: string;
  updated_at: string;
}

export type MessageDirection = 'inbound' | 'outbound';
export type MessageAuthorType = 'agent' | 'customer' | 'system' | 'bot';
export type MessageChannel = 'email' | 'whatsapp' | 'sms' | 'chat' | 'portal' | 'internal';
export type MessageDeliveryState = 'queued' | 'sent' | 'delivered' | 'read' | 'failed';

export interface TicketMessageAttachment {
  uuid: string;
  original_name: string;
  mime_type: string;
  size_bytes: number;
  scan_state: 'pending' | 'clean' | 'infected' | 'failed';
  created_at: string;
}

/**
 * `TicketMessageController::store()` returns a properly-wrapped
 * TicketMessageResource (so `useSendTicketMessage`'s result has `author` and
 * `attachments`). `TicketMessageController::index()` does NOT — it returns
 * `ApiResponse::collection($paginated, ...)` with the raw paginator items, no
 * `TicketMessageResource::collection()` wrapping. So the *list* of messages
 * lacks `author` (relation not loaded/serialized) and `attachments`
 * (relation not loaded) entirely, and exposes `author_user_id` as a raw,
 * client-unresolvable internal integer instead of a UUID. This type reflects
 * what GET /tickets/{ticket}/messages actually returns.
 */
export interface TicketMessage {
  uuid: string;
  direction: MessageDirection;
  author_type: MessageAuthorType;
  author_user_id: number | null;
  channel: MessageChannel;
  is_internal: boolean;
  body: string;
  body_format: string;
  delivery_state: MessageDeliveryState | null;
  failure_reason: string | null;
  retry_count: number;
  queued_at: string | null;
  sent_at: string | null;
  delivered_at: string | null;
  read_at: string | null;
  failed_at: string | null;
  created_at: string;
}

export interface TicketMessageDeliveryEvent {
  uuid: string;
  from_state: MessageDeliveryState | null;
  to_state: MessageDeliveryState;
  reason: string | null;
  occurred_at: string;
}

/**
 * `TicketController::history()` returns raw `TicketEvent` models (no
 * TicketEventResource wrapping — same pattern as the list endpoints).
 * `actor_user_id` is a raw internal integer FK, not resolvable to a UUID.
 */
export interface TicketEvent {
  id: number;
  uuid: string;
  type: string | null;
  actor_user_id: number | null;
  payload: Record<string, unknown> | null;
  occurred_at: string | null;
  created_at: string;
}

/**
 * `TicketLinkController::index()` also returns raw models. `source_ticket_id`
 * / `target_ticket_id` are raw internal integers referencing `tickets.id`,
 * not the ticket UUID — there is no client-resolvable way to link to the
 * related ticket from this response.
 */
export interface TicketLink {
  id: number;
  uuid: string;
  source_ticket_id: number;
  target_ticket_id: number;
  relation: 'related' | 'duplicate_of' | 'blocks';
  created_by_user_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface TicketWatcher {
  uuid: string;
  name: string;
}

export interface TicketWatchersResponse {
  watchers: TicketWatcher[];
}

export interface TicketCategory {
  id: string;
  code: string;
  name: BilingualValue;
  parent_id: string | null;
  depth: number;
  is_active: boolean;
}
