// Adapter layer between the workspace UI and the generated Workspace HTTP
// client, following the same pattern as frontend/src/features/tickets/api/wire.ts:
// the orval-generated request-body types here are generated from
// docs/api/openapi.yaml, which has drifted from what the Laravel FormRequests
// actually validate and what the Controllers actually return. This file is the
// single place that knows the real wire shape — components must never import
// generated model types directly.
//
// Known mismatches (generated type -> real backend), verified live via
// php artisan tinker against the running app (see .squad/gaps/35-482.md):
//   - PostAgentTasksBody:        { title, description }         -> StoreAgentTaskRequest: { owner_id (required), title, description?, due_at?, due_in_working_time?, branch_id?, ticket_id? }
//   - GetAgentTasksParams:       { page, per_page, sort }        -> AgentTaskController::index() also accepts filter[state|owner_id|ticket_id|due_before|due_after] and filter[overdue]=1
//   - PostAgentTaskStateBody.state enum: pending|in_progress|completed|cancelled -> AgentTaskState enum: open|in_progress|done|cancelled
//   - PostQuickRepliesRenderBody: { template, context }          -> real request: { reply_id (uuid, required), ticket_id? (uuid) }
//   - PostQuickRepliesRender200:  { data: { output } }           -> real response: { data: { en, ar } }
//
// due_at must be sent as ISO-8601 WITH a literal numeric offset
// (StoreAgentTaskRequest validates `date_format:Y-m-d\TH:i:sP` — the
// PHP `date_format:c` originally used there was unsatisfiable by any real
// input, see gap file #7, and has been fixed to this literal format).
// Date.prototype.toISOString() produces a `Z` suffix, NOT a numeric offset,
// and will be rejected — always format with formatWithOffset() below.

import { useMutation, type UseMutationOptions } from '@tanstack/react-query';
import {
  getAgentTasks,
  postAgentTasks,
  patchAgentTask,
  deleteAgentTask,
  postAgentTaskState,
} from '@/api/generated/workspace/workspace';
import { getQuickReplies, postQuickRepliesRender } from '@/api/generated/workspace/workspace';
import type { AgentTask, AgentTaskState, QuickReplySummary, RenderedQuickReply } from '../types';

const JSON_HEADERS = { 'Content-Type': 'application/json' } as const;

export function newIdempotencyKey(): string {
  return crypto.randomUUID();
}

/** Formats a Date as ISO-8601 with a literal numeric UTC offset, e.g. 2026-08-27T08:42:20+00:00. */
export function formatWithOffset(date: Date): string {
  const pad = (n: number) => String(n).padStart(2, '0');
  const offsetMinutesTotal = -date.getTimezoneOffset();
  const sign = offsetMinutesTotal >= 0 ? '+' : '-';
  const offsetHours = pad(Math.floor(Math.abs(offsetMinutesTotal) / 60));
  const offsetMinutes = pad(Math.abs(offsetMinutesTotal) % 60);

  return (
    `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}` +
    `T${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}` +
    `${sign}${offsetHours}:${offsetMinutes}`
  );
}

/** Converts a <input type="datetime-local"> value (no offset) to the wire format. */
export function localDateTimeToWire(value: string): string | null {
  if (!value) return null;
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return null;
  return formatWithOffset(date);
}

function mapAgentTaskRaw(raw: Record<string, unknown>): AgentTask {
  const owner = (raw.owner as Record<string, unknown> | null | undefined) ?? null;
  const ticket = raw.ticket as Record<string, unknown> | null | undefined;

  return {
    uuid: String(raw.uuid),
    title: String(raw.title ?? ''),
    description: (raw.description as string | null) ?? null,
    state: raw.state as AgentTaskState,
    dueAt: (raw.due_at as string | null) ?? null,
    completedAt: (raw.completed_at as string | null) ?? null,
    cancelledAt: (raw.cancelled_at as string | null) ?? null,
    isOverdue: Boolean(raw.is_overdue),
    owner: {
      uuid: (owner?.uuid as string | null) ?? null,
      name: (owner?.name as string | null) ?? null,
    },
    ticket: ticket ? { uuid: String(ticket.uuid), reference: String(ticket.reference) } : null,
    createdAt: String(raw.created_at ?? ''),
    updatedAt: String(raw.updated_at ?? ''),
  };
}

export function mapAgentTask(raw: unknown): AgentTask {
  return mapAgentTaskRaw(raw as Record<string, unknown>);
}

export interface CreateTaskInput {
  ownerId: string;
  title: string;
  description?: string | null;
  dueAt?: string | null; // already wire-formatted (use formatWithOffset/localDateTimeToWire before calling)
  dueInWorkingTime?: boolean;
  ticketId?: string | null;
  branchId?: string | null;
}

export function toCreateTaskPayload(input: CreateTaskInput): Record<string, unknown> {
  return {
    owner_id: input.ownerId,
    title: input.title,
    description: input.description ?? null,
    due_at: input.dueAt ?? null,
    due_in_working_time: input.dueInWorkingTime ?? false,
    ticket_id: input.ticketId ?? null,
    branch_id: input.branchId ?? null,
  };
}

export interface UpdateTaskInput {
  title?: string;
  description?: string | null;
  dueAt?: string | null;
}

export function toUpdateTaskPayload(input: UpdateTaskInput): Record<string, unknown> {
  const payload: Record<string, unknown> = {};
  if (input.title !== undefined) payload.title = input.title;
  if (input.description !== undefined) payload.description = input.description;
  if (input.dueAt !== undefined) payload.due_at = input.dueAt;
  return payload;
}

export interface AgentTaskListParams {
  page?: number;
  perPage?: number;
  sort?: string;
  state?: AgentTaskState;
  ownerId?: string; // user uuid — backend resolves to internal id
  ticketId?: string; // ticket uuid — backend resolves to internal id
  overdue?: boolean;
}

export function toAgentTaskListParams(params: AgentTaskListParams): Record<string, unknown> {
  const filter: Record<string, unknown> = {};
  if (params.state) filter.state = { eq: params.state };
  if (params.ownerId) filter.owner_id = { eq: params.ownerId };
  if (params.ticketId) filter.ticket_id = { eq: params.ticketId };
  if (params.overdue) filter.overdue = 1;

  return {
    page: params.page,
    per_page: params.perPage,
    sort: params.sort,
    filter: Object.keys(filter).length > 0 ? filter : undefined,
  };
}

export async function fetchAgentTasks(params: AgentTaskListParams) {
  const result = (await getAgentTasks(toAgentTaskListParams(params) as never)) as unknown as {
    items: Record<string, unknown>[];
    meta: Record<string, unknown>;
  };
  return { items: result.items.map(mapAgentTaskRaw), meta: result.meta };
}

export function useCreateAgentTask(
  options?: UseMutationOptions<AgentTask, unknown, CreateTaskInput & { idempotencyKey: string }>
) {
  return useMutation({
    mutationFn: async ({ idempotencyKey, ...input }) => {
      const result = await postAgentTasks(toCreateTaskPayload(input) as never, {
        headers: { ...JSON_HEADERS, 'Idempotency-Key': idempotencyKey },
      });
      return mapAgentTask((result as { data: unknown }).data ?? result);
    },
    ...options,
  });
}

export function useUpdateAgentTask(
  options?: UseMutationOptions<
    unknown,
    unknown,
    { task: string; input: UpdateTaskInput; idempotencyKey: string }
  >
) {
  return useMutation({
    mutationFn: ({ task, input, idempotencyKey }) =>
      patchAgentTask(task, toUpdateTaskPayload(input) as never, {
        headers: { ...JSON_HEADERS, 'Idempotency-Key': idempotencyKey },
      }),
    ...options,
  });
}

export function useChangeAgentTaskState(
  options?: UseMutationOptions<
    AgentTask,
    unknown,
    { task: string; state: AgentTaskState; idempotencyKey: string }
  >
) {
  return useMutation({
    mutationFn: async ({ task, state, idempotencyKey }) => {
      const result = await postAgentTaskState(
        task,
        { state } as never,
        { headers: { ...JSON_HEADERS, 'Idempotency-Key': idempotencyKey } }
      );
      return mapAgentTask((result as unknown as { data: unknown }).data ?? result);
    },
    ...options,
  });
}

export function useDeleteAgentTask(options?: UseMutationOptions<unknown, unknown, { task: string }>) {
  return useMutation({
    mutationFn: ({ task }) => deleteAgentTask(task),
    ...options,
  });
}

// --- Quick replies -----------------------------------------------------------

function mapQuickReplyRaw(raw: Record<string, unknown>): QuickReplySummary {
  return {
    uuid: String(raw.uuid),
    scope: raw.scope as 'personal' | 'shared',
    title: raw.title as QuickReplySummary['title'],
    isActive: Boolean(raw.is_active),
  };
}

export function mapQuickReply(raw: unknown): QuickReplySummary {
  return mapQuickReplyRaw(raw as Record<string, unknown>);
}

export async function fetchQuickReplies(params: { page?: number; perPage?: number }) {
  const result = (await getQuickReplies({
    page: params.page,
    per_page: params.perPage,
  } as never)) as unknown as { items: Record<string, unknown>[]; meta: Record<string, unknown> };
  return { items: result.items.map(mapQuickReplyRaw), meta: result.meta };
}

export function mapRenderedQuickReply(raw: unknown): RenderedQuickReply {
  const data = raw as { en?: string; ar?: string };
  return { en: data.en ?? '', ar: data.ar ?? '' };
}

export function useRenderQuickReply(
  options?: UseMutationOptions<RenderedQuickReply, unknown, { replyId: string; ticketId?: string | null }>
) {
  return useMutation({
    mutationFn: async ({ replyId, ticketId }) => {
      const result = await postQuickRepliesRender(
        { reply_id: replyId, ticket_id: ticketId ?? undefined } as never
      );
      return mapRenderedQuickReply((result as { data: unknown }).data ?? result);
    },
    ...options,
  });
}
