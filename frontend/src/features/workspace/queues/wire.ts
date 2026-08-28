// tickets/queues/mine and tickets/queues/department/{department} return raw
// Eloquent Ticket rows, NOT TicketResource-wrapped data (confirmed live — see
// .squad/gaps/35-482.md #19). frontend/src/features/tickets/types.ts already
// documents this exact shape as TicketListRow; we reuse that type directly
// rather than inventing a new one for the same shape. There is no existing
// runtime mapper for it in tickets/api/wire.ts (the story plan's claim that
// one exists there does not hold up — it doesn't), so mapQueueTicket lives here.
//
// No SLA fields ever reach these rows (TicketResource's sla block requires the
// slaClocks relation, which isn't in either endpoint's allowed includes, and
// these endpoints don't use TicketResource anyway) — SlaRiskPanel renders an
// EmptyState instead of attempting to read one.

import type { TicketListRow } from '../../tickets/types';

export function mapQueueTicket(raw: unknown): TicketListRow {
  return raw as TicketListRow;
}

export interface QueueListParams {
  page?: number;
  perPage?: number;
  sort?: string;
}

export function toQueueListParams(params: QueueListParams): Record<string, unknown> {
  return {
    page: params.page,
    per_page: params.perPage,
    sort: params.sort ?? '-updated_at',
  };
}
