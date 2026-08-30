// Adapter layer between the ticket UI and the ticketing HTTP API.
//
// The orval-generated request-body types under `@/api/generated/model` are
// generated from `docs/api/openapi.yaml`, which has drifted from what the
// Laravel FormRequests actually validate (confirmed against
// `tests/Feature/Ticketing/TicketInternalNoteVisibilityTest.php`, which posts
// `{ channel, is_internal }` with no `visibility` field at all). Sending the
// generated shape verbatim would silently break several flows — most
// importantly, an "internal" note would be sent as an ordinary public one,
// since the backend never reads a `visibility` field.
//
// This file is the single place that knows the real wire shape. It reuses
// the generated functions for URL/method construction (still correct) and
// casts past their body types where they diverge from the FormRequest rules.
// Known mismatches (generated type -> real FormRequest, verified by reading
// the FormRequest `rules()` in app/Domains/Ticketing/Http/Requests/):
//   - postTicketMessagesBody:      { visibility }        -> StoreTicketMessageRequest: { channel, is_internal }
//   - postTicketStatusBody:        { status_id }         -> ChangeTicketStatusRequest: { status, reason? }
//   - postTicketAssignBody:        { user_id }           -> AssignTicketRequest: { assignee_uuid?, version? }
//   - postTicketTransferAgentBody: { user_id }           -> TransferTicketToAgentRequest: { user_uuid, version? }
//   - postTicketTransferDepartmentBody: missing fields   -> TransferTicketToDepartmentRequest: { department_id, keep_assignee?, version? }
//   - postTicketLinksBody:         { related_ticket_id, link_type } -> StoreTicketLinkRequest: { target, relation }
//   - claim/reopen/markSpam/restoreFromSpam: generated functions take no body -> ClaimTicketRequest needs { version? }, Reopen/MarkSpam/Restore need required { reason }
// Merge/split are intentionally not wired here — not required by this story's
// Done Criteria, and split's generated body (`message_ids`) has no relation
// to the real SplitTicketRequest (`subject`, `body`, `category`), which would
// need its own product decision.
//
// If the backend or the spec is fixed to match, this file (and this comment)
// is the only place that needs to change.

import { useMutation, type UseMutationOptions } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import {
  postTicketStatus,
  postTicketAssign,
  deleteTicketUnassign,
  postTicketTransferAgent,
  postTicketTransferDepartment,
  postTicketMessages,
  postTicketLinks,
  postTicketWatchers,
  deleteTicketWatcher,
  postTicketMessageRetry,
  patchTicket,
} from '@/api/generated/ticketing/ticketing';
import type { PostTicketStatusBody } from '@/api/generated/model/postTicketStatusBody';
import type { PostTicketAssignBody } from '@/api/generated/model/postTicketAssignBody';
import type { PostTicketTransferAgentBody } from '@/api/generated/model/postTicketTransferAgentBody';
import type { PostTicketTransferDepartmentBody } from '@/api/generated/model/postTicketTransferDepartmentBody';
import type { PostTicketMessagesBody } from '@/api/generated/model/postTicketMessagesBody';
import type { PostTicketLinksBody } from '@/api/generated/model/postTicketLinksBody';
import type { PostTicketWatchersBody } from '@/api/generated/model/postTicketWatchersBody';
import type { MessageChannel, TicketLink, TicketMessage, TicketDetail } from '../types';

const JSON_HEADERS = { 'Content-Type': 'application/json' } as const;

export function newIdempotencyKey(): string {
  return crypto.randomUUID();
}

// --- Status --------------------------------------------------------------

export function changeTicketStatus(ticket: string, statusId: string, reason?: string): Promise<TicketDetail> {
  return postTicketStatus(
    ticket,
    { status: statusId, reason } as unknown as PostTicketStatusBody,
    { headers: JSON_HEADERS }
  ) as unknown as Promise<TicketDetail>;
}

export function useChangeTicketStatus(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; statusId: string; reason?: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, statusId, reason }) => changeTicketStatus(ticket, statusId, reason),
    ...options,
  });
}

// --- Assignment ------------------------------------------------------------

export function assignTicket(ticket: string, assigneeUuid: string, version?: number): Promise<TicketDetail> {
  return postTicketAssign(
    ticket,
    { assignee_uuid: assigneeUuid, version } as unknown as PostTicketAssignBody,
    { headers: JSON_HEADERS }
  ) as unknown as Promise<TicketDetail>;
}

export function useAssignTicket(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; assigneeUuid: string; version?: number }>
) {
  return useMutation({
    mutationFn: ({ ticket, assigneeUuid, version }) => assignTicket(ticket, assigneeUuid, version),
    ...options,
  });
}

export function useUnassignTicket(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string }>
) {
  return useMutation({
    mutationFn: ({ ticket }) => deleteTicketUnassign(ticket) as unknown as Promise<TicketDetail>,
    ...options,
  });
}

export function useClaimTicket(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; version?: number }>
) {
  return useMutation({
    mutationFn: ({ ticket, version }) =>
      apiRequest<TicketDetail>({
        url: `/tickets/${ticket}/claim`,
        method: 'POST',
        headers: JSON_HEADERS,
        data: { version },
      }),
    ...options,
  });
}

export function useTransferTicketToAgent(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; userUuid: string; version?: number }>
) {
  return useMutation({
    mutationFn: ({ ticket, userUuid, version }) =>
      postTicketTransferAgent(
        ticket,
        { user_uuid: userUuid, version } as unknown as PostTicketTransferAgentBody,
        { headers: JSON_HEADERS }
      ) as unknown as Promise<TicketDetail>,
    ...options,
  });
}

export function useTransferTicketToDepartment(
  options?: UseMutationOptions<
    TicketDetail,
    unknown,
    { ticket: string; departmentId: string; keepAssignee?: boolean; version?: number }
  >
) {
  return useMutation({
    mutationFn: ({ ticket, departmentId, keepAssignee, version }) =>
      postTicketTransferDepartment(
        ticket,
        {
          department_id: departmentId,
          keep_assignee: keepAssignee,
          version,
        } as unknown as PostTicketTransferDepartmentBody,
        { headers: JSON_HEADERS }
      ) as unknown as Promise<TicketDetail>,
    ...options,
  });
}

// --- Properties --------------------------------------------------------------
// UpdateTicketRequest::rules() only accepts subject/body/priority/status —
// there is no endpoint to change category, department, tags or custom fields
// on an existing ticket (no route in routes/api.php for reclassify/tag despite
// the tickets.reclassify / tickets.tag permission keys existing). Priority is
// the only ticket property this story can offer an edit control for.

export function useUpdateTicketPriority(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; priority: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, priority }) =>
      patchTicket(ticket, { priority }, { headers: JSON_HEADERS }) as unknown as Promise<TicketDetail>,
    ...options,
  });
}

// --- Lifecycle (reopen / spam) — generated functions carry no body at all,
// but the backend requires a `reason`. Call apiRequest directly. -----------

export function useReopenTicket(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; reason: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, reason }) =>
      apiRequest<TicketDetail>({
        url: `/tickets/${ticket}/reopen`,
        method: 'POST',
        headers: JSON_HEADERS,
        data: { reason },
      }),
    ...options,
  });
}

export function useMarkTicketSpam(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; reason: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, reason }) =>
      apiRequest<TicketDetail>({
        url: `/tickets/${ticket}/spam`,
        method: 'POST',
        headers: JSON_HEADERS,
        data: { reason },
      }),
    ...options,
  });
}

export function useRestoreTicketFromSpam(
  options?: UseMutationOptions<TicketDetail, unknown, { ticket: string; reason: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, reason }) =>
      apiRequest<TicketDetail>({
        url: `/tickets/${ticket}/spam`,
        method: 'DELETE',
        headers: JSON_HEADERS,
        data: { reason },
      }),
    ...options,
  });
}

// --- Messages ---------------------------------------------------------------

export interface SendTicketMessageVars {
  ticket: string;
  body: string;
  isInternal: boolean;
  channel: MessageChannel;
  attachmentUuids?: string[];
  mentions?: string[];
  idempotencyKey: string;
}

export function useSendTicketMessage(
  options?: UseMutationOptions<TicketMessage, unknown, SendTicketMessageVars>
) {
  return useMutation({
    mutationFn: ({ ticket, body, isInternal, channel, attachmentUuids, mentions, idempotencyKey }) =>
      postTicketMessages(
        ticket,
        {
          body,
          is_internal: isInternal,
          channel,
          attachment_uuids: attachmentUuids,
          mentions,
        } as unknown as PostTicketMessagesBody,
        { headers: { ...JSON_HEADERS, 'Idempotency-Key': idempotencyKey } }
      ) as unknown as Promise<TicketMessage>,
    ...options,
  });
}

export function useRetryTicketMessage(
  options?: UseMutationOptions<TicketMessage, unknown, { ticket: string; message: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, message }) =>
      postTicketMessageRetry(ticket, message) as unknown as Promise<TicketMessage>,
    ...options,
  });
}

// --- Links -------------------------------------------------------------------

export function useCreateTicketLink(
  options?: UseMutationOptions<TicketLink, unknown, { ticket: string; target: string; relation: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, target, relation }) =>
      postTicketLinks(
        ticket,
        { target, relation } as unknown as PostTicketLinksBody,
        { headers: JSON_HEADERS }
      ) as unknown as Promise<TicketLink>,
    ...options,
  });
}

// --- Watchers (self-watch only; the backend ignores any user in the body) --

export function useWatchTicket(options?: UseMutationOptions<unknown, unknown, { ticket: string }>) {
  return useMutation({
    mutationFn: ({ ticket }) =>
      postTicketWatchers(ticket, {} as unknown as PostTicketWatchersBody, { headers: JSON_HEADERS }),
    ...options,
  });
}

export function useUnwatchTicket(
  options?: UseMutationOptions<unknown, unknown, { ticket: string; userId: string }>
) {
  return useMutation({
    mutationFn: ({ ticket, userId }) => deleteTicketWatcher(ticket, userId),
    ...options,
  });
}
