// Adapter layer between the customer UI and the customers HTTP API.
//
// The orval-generated request-body types under `@/api/generated/model` are
// generated from `docs/api/openapi.yaml`, which has drifted from what the
// Laravel FormRequests in `app/Domains/Customers/Http/Requests/` actually
// validate (confirmed by reading each FormRequest's `rules()` directly —
// see .squad/gaps/34-481.md #10 for the full list). Known mismatches:
//   - postCustomerNotesBody:   { content }              -> StoreCustomerNoteRequest: { body }
//   - postCustomerMergeBody:   { target_customer_id }    -> MergeCustomerRequest: { duplicate_customer_uuid }
//     (the route's {customer} is the SURVIVOR; duplicate_customer_uuid is the absorbed/loser —
//     confirmed from CustomerMergeController::store())
//   - postCustomerBlockBody:   { reason?: string }        -> BlockCustomerRequest: { reason } (required)
//   - postCustomerAttachmentsBody: { file: Blob } (multipart) -> StoreCustomerAttachmentRequest: { attachment_uuid }
//     (the file is uploaded FIRST to the generic POST /attachments endpoint,
//     then this endpoint just links that attachment's uuid to the customer —
//     same two-step flow as frontend/src/shared/attachments/AttachmentUploader.tsx)
//
// Also: several generated response types are typed as `void` / untyped
// placeholders because the OpenAPI spec declares generic schemas for these
// endpoints. This file's mapper functions (toCustomerSummary, etc.) fill
// that gap — see ../types.ts for the authoritative shapes, each verified
// against the real controller/resource, some empirically via a live
// request (see .squad/gaps/34-481.md).
//
// If the backend or the spec is fixed to match, this file (and this
// comment) is the only place that needs to change.

import { useMutation, type UseMutationOptions } from '@tanstack/react-query';
import {
  postCustomerBlock,
  postCustomerUnblock,
  postCustomerContacts,
  putCustomerContact,
  deleteCustomerContact,
  postCustomerNotes,
  deleteCustomerNote,
  postCustomerAttachments,
  deleteCustomerAttachment,
  postCustomerDuplicateDismiss,
  postCustomerMerge,
} from '@/api/generated/customers/customers';
import type { PostCustomerBlockBody } from '@/api/generated/model/postCustomerBlockBody';
import type { PostCustomerContactsBody } from '@/api/generated/model/postCustomerContactsBody';
import type { PutCustomerContactBody } from '@/api/generated/model/putCustomerContactBody';
import type { PostCustomerNotesBody } from '@/api/generated/model/postCustomerNotesBody';
import type { PostCustomerAttachmentsBody } from '@/api/generated/model/postCustomerAttachmentsBody';
import type { PostCustomerMergeBody } from '@/api/generated/model/postCustomerMergeBody';
import type {
  CustomerDetail,
  CustomerListRow,
  CustomerContact,
  CustomerNote,
  CustomerAttachment,
  CustomerCompanyAccount,
  TimelineEntry,
  DuplicateCandidate,
  DuplicateCandidateParty,
} from '../types';

const JSON_HEADERS = { 'Content-Type': 'application/json' } as const;

// --- Mappers (pure, no React) ------------------------------------------

function str(value: unknown): string {
  return typeof value === 'string' ? value : '';
}

function strOrNull(value: unknown): string | null {
  return typeof value === 'string' ? value : null;
}

function num(value: unknown): number {
  return typeof value === 'number' ? value : Number(value ?? 0);
}

function numOrNull(value: unknown): number | null {
  return typeof value === 'number' ? value : null;
}

export function toCustomerCompanyAccount(raw: unknown): CustomerCompanyAccount | null {
  if (!raw || typeof raw !== 'object') return null;
  const data = raw as Record<string, unknown>;
  if (!data.uuid) return null;
  return {
    uuid: str(data.uuid),
    name: str(data.name),
    serviceTier: strOrNull(data.service_tier),
  };
}

export function toCustomerContact(raw: unknown): CustomerContact {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    uuid: str(data.uuid),
    type: str(data.type),
    value: str(data.value),
    label: strOrNull(data.label),
    isPrimary: Boolean(data.is_primary),
    verifiedAt: strOrNull(data.verified_at),
  };
}

export function toCustomerDetail(raw: unknown): CustomerDetail {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    uuid: str(data.uuid),
    name: str(data.name),
    preferredLocale: str(data.preferred_locale) || 'en',
    status: (str(data.status) || 'active') as CustomerDetail['status'],
    blockedReason: strOrNull(data.blocked_reason),
    blockedAt: strOrNull(data.blocked_at),
    companyAccount: toCustomerCompanyAccount(data.company_account),
    contacts: Array.isArray(data.contacts) ? data.contacts.map(toCustomerContact) : [],
    createdAt: strOrNull(data.created_at),
    updatedAt: strOrNull(data.updated_at),
  };
}

/** See ../types.ts CustomerListRow doc comment: raw Eloquent columns, not CustomerResource. */
export function toCustomerListRow(raw: unknown): CustomerListRow {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: num(data.id),
    uuid: str(data.uuid),
    name: str(data.name),
    nameNormalised: str(data.name_normalised),
    companyAccountId: numOrNull(data.company_account_id),
    preferredLocale: str(data.preferred_locale) || 'en',
    status: (str(data.status) || 'active') as CustomerListRow['status'],
    blockedReason: strOrNull(data.blocked_reason),
    blockedAt: strOrNull(data.blocked_at),
    blockedByUserId: numOrNull(data.blocked_by_user_id),
    anonymisedAt: strOrNull(data.anonymised_at),
    mergedIntoCustomerId: numOrNull(data.merged_into_customer_id),
    mergedAt: strOrNull(data.merged_at),
    createdAt: str(data.created_at),
    updatedAt: str(data.updated_at),
  };
}

export function toCustomerNote(raw: unknown): CustomerNote {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    uuid: str(data.uuid),
    body: str(data.body),
    authorUuid: strOrNull(data.author_uuid),
    authorName: strOrNull(data.author_name),
    createdAt: str(data.created_at),
  };
}

export function toCustomerAttachment(raw: unknown): CustomerAttachment {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    uuid: str(data.uuid),
    originalName: str(data.original_name),
    mimeType: str(data.mime_type),
    sizeBytes: num(data.size_bytes),
    scanState: (str(data.scan_state) || 'pending') as CustomerAttachment['scanState'],
    createdAt: str(data.created_at),
  };
}

export function toTimelineEntry(raw: unknown): TimelineEntry {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    source: str(data.source),
    type: str(data.type),
    occurredAt: str(data.occurred_at),
    actorUuid: strOrNull(data.actor_uuid),
    payload: (data.payload ?? null) as Record<string, unknown> | null,
  };
}

function toDuplicateCandidateParty(raw: unknown): DuplicateCandidateParty {
  const data = (raw ?? {}) as Record<string, unknown>;
  return { uuid: str(data.uuid), name: str(data.name) };
}

export function toDuplicateCandidate(raw: unknown): DuplicateCandidate {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    uuid: str(data.uuid),
    customer: toDuplicateCandidateParty(data.customer),
    duplicateCustomer: toDuplicateCandidateParty(data.duplicate_customer),
    status: str(data.status),
    rule: strOrNull(data.rule),
    evidence: (data.evidence ?? null) as Record<string, unknown> | null,
    reviewedAt: strOrNull(data.reviewed_at),
    createdAt: str(data.created_at),
  };
}

// --- Mutations -----------------------------------------------------------

export function useBlockCustomer(
  options?: UseMutationOptions<CustomerDetail, unknown, { customer: string; reason: string }>
) {
  return useMutation({
    mutationFn: ({ customer, reason }) =>
      postCustomerBlock(customer, { reason } as unknown as PostCustomerBlockBody, {
        headers: JSON_HEADERS,
      }).then(toCustomerDetail),
    ...options,
  });
}

export function useUnblockCustomer(
  options?: UseMutationOptions<CustomerDetail, unknown, { customer: string }>
) {
  return useMutation({
    mutationFn: ({ customer }) => postCustomerUnblock(customer).then(toCustomerDetail),
    ...options,
  });
}

export interface AddCustomerContactVars {
  customer: string;
  type: string;
  value: string;
  label?: string;
  isPrimary: boolean;
}

export function useAddCustomerContact(
  options?: UseMutationOptions<CustomerContact, unknown, AddCustomerContactVars>
) {
  return useMutation({
    mutationFn: ({ customer, type, value, label, isPrimary }) =>
      postCustomerContacts(
        customer,
        { type, value, label, is_primary: isPrimary } as unknown as PostCustomerContactsBody,
        { headers: JSON_HEADERS }
      ).then(toCustomerContact),
    ...options,
  });
}

export interface UpdateCustomerContactVars {
  customer: string;
  contact: string;
  value?: string;
  label?: string;
  isPrimary?: boolean;
}

export function useUpdateCustomerContact(
  options?: UseMutationOptions<CustomerContact, unknown, UpdateCustomerContactVars>
) {
  return useMutation({
    mutationFn: ({ customer, contact, value, label, isPrimary }) =>
      putCustomerContact(
        customer,
        contact,
        { value, label, is_primary: isPrimary } as unknown as PutCustomerContactBody,
        { headers: JSON_HEADERS }
      ).then(toCustomerContact),
    ...options,
  });
}

export function useRemoveCustomerContact(
  options?: UseMutationOptions<unknown, unknown, { customer: string; contact: string }>
) {
  return useMutation({
    mutationFn: ({ customer, contact }) => deleteCustomerContact(customer, contact),
    ...options,
  });
}

export function useAddCustomerNote(
  options?: UseMutationOptions<CustomerNote, unknown, { customer: string; body: string }>
) {
  return useMutation({
    mutationFn: ({ customer, body }) =>
      postCustomerNotes(customer, { body } as unknown as PostCustomerNotesBody, {
        headers: JSON_HEADERS,
      }).then(toCustomerNote),
    ...options,
  });
}

export function useRemoveCustomerNote(
  options?: UseMutationOptions<unknown, unknown, { customer: string; note: string }>
) {
  return useMutation({
    mutationFn: ({ customer, note }) => deleteCustomerNote(customer, note),
    ...options,
  });
}

export function useLinkCustomerAttachment(
  options?: UseMutationOptions<CustomerAttachment, unknown, { customer: string; attachmentUuid: string }>
) {
  return useMutation({
    mutationFn: ({ customer, attachmentUuid }) =>
      postCustomerAttachments(
        customer,
        { attachment_uuid: attachmentUuid } as unknown as PostCustomerAttachmentsBody,
        { headers: JSON_HEADERS }
      ).then(toCustomerAttachment),
    ...options,
  });
}

export function useRemoveCustomerAttachment(
  options?: UseMutationOptions<unknown, unknown, { customer: string; attachment: string }>
) {
  return useMutation({
    mutationFn: ({ customer, attachment }) => deleteCustomerAttachment(customer, attachment),
    ...options,
  });
}

export function useDismissDuplicateCandidate(
  options?: UseMutationOptions<DuplicateCandidate, unknown, { candidate: string }>
) {
  return useMutation({
    mutationFn: ({ candidate }) => postCustomerDuplicateDismiss(candidate).then(toDuplicateCandidate),
    ...options,
  });
}

export function useMergeCustomers(
  options?: UseMutationOptions<CustomerDetail, unknown, { survivor: string; absorbed: string }>
) {
  return useMutation({
    mutationFn: ({ survivor, absorbed }) =>
      postCustomerMerge(
        survivor,
        { duplicate_customer_uuid: absorbed } as unknown as PostCustomerMergeBody,
        { headers: JSON_HEADERS }
      ).then(toCustomerDetail),
    ...options,
  });
}
