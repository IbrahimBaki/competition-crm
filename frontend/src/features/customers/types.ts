// Local response types mirroring what the backend actually returns — not
// always the same as the PHP JsonResource classes. Keep in sync with:
//   app/Domains/Customers/Http/Resources/CustomerResource.php
//   app/Domains/Customers/Http/Resources/CustomerContactResource.php
//   app/Domains/Customers/Http/Resources/CustomerNoteResource.php
//   app/Domains/Customers/Http/Resources/CustomerAttachmentResource.php
//   app/Domains/Customers/Http/Resources/CustomerCompanyAccountResource.php
//   app/Domains/Customers/Http/Resources/CustomerDuplicateCandidateResource.php
//   app/Domains/Customers/Services/Timeline/TimelineEntry.php (via TimelineEntryResource)
//
// `status` only ever takes these three values — see
// app/Domains/Customers/Models/CustomerStatus.php. There is no "merged"
// status; being merged is tracked separately via merged_into_customer_id
// (see CustomerListRow below and .squad/gaps/34-481.md #6).
export type CustomerStatus = 'active' | 'blocked' | 'anonymised';

export interface CustomerContact {
  uuid: string;
  type: string; // ContactType enum value, e.g. "email" | "phone" | ...
  value: string;
  label: string | null;
  isPrimary: boolean;
  verifiedAt: string | null;
}

export interface CustomerCompanyAccount {
  uuid: string;
  name: string;
  serviceTier: string | null;
}

/**
 * `CustomerController::show()` return shape (CustomerResource). `name` is a
 * plain string on this resource — the backend does not store a bilingual
 * customer name (see StoreCustomerRequest::rules(): 'name' => 'string').
 * There is no `service_tier` field here; it lives on `companyAccount`.
 */
export interface CustomerDetail {
  uuid: string;
  name: string;
  preferredLocale: string;
  status: CustomerStatus;
  blockedReason: string | null;
  blockedAt: string | null;
  companyAccount: CustomerCompanyAccount | null;
  contacts: CustomerContact[];
  createdAt: string | null;
  updatedAt: string | null;
}

/**
 * `CustomerController::index()` does NOT wrap rows in CustomerResource — it
 * passes raw Eloquent `Customer` models straight to `ApiResponse::collection()`
 * (confirmed live; see .squad/gaps/34-481.md #7). Every DB column serializes,
 * including internal integer FKs, so this type mirrors the migrations
 * (`customers` table + the merge-columns migration), not CustomerResource.
 * `id` is the internal auto-increment PK — never use it as an identifier,
 * always use `uuid`. `companyAccountId`/`blockedByUserId`/
 * `mergedIntoCustomerId` are raw integer FKs with no client-resolvable UUID
 * on this endpoint, so they cannot be rendered as names/links — only
 * presence/absence is usable (e.g. the merged badge).
 */
export interface CustomerListRow {
  id: number;
  uuid: string;
  name: string;
  nameNormalised: string;
  companyAccountId: number | null;
  preferredLocale: string;
  status: CustomerStatus;
  blockedReason: string | null;
  blockedAt: string | null;
  blockedByUserId: number | null;
  anonymisedAt: string | null;
  mergedIntoCustomerId: number | null;
  mergedAt: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface CustomerNote {
  uuid: string;
  body: string;
  authorUuid: string | null;
  authorName: string | null;
  createdAt: string;
}

export type AttachmentScanState = 'pending' | 'clean' | 'infected' | 'failed';

export interface CustomerAttachment {
  uuid: string;
  originalName: string;
  mimeType: string;
  sizeBytes: number;
  scanState: AttachmentScanState;
  createdAt: string;
}

/**
 * Mirrors TimelineEntryResource exactly: {id, source, type, occurred_at,
 * actor_uuid, payload}. There is no `title`/`summary`/`channel`/`ticket_id`
 * at the top level — those are derived client-side from `source`/`type`/
 * `payload`, and must degrade gracefully for a `source`/`type` combination
 * the frontend doesn't recognise yet (see .squad/gaps/34-481.md #9).
 */
export interface TimelineEntry {
  id: string;
  source: string;
  type: string;
  occurredAt: string;
  actorUuid: string | null;
  payload: Record<string, unknown> | null;
}

/**
 * Mirrors CustomerDuplicateCandidateResource exactly — there is no `score`
 * field (the plan's draft type invented one); the backend exposes `rule`
 * and `evidence` instead.
 */
export interface DuplicateCandidateParty {
  uuid: string;
  name: string;
}

export interface DuplicateCandidate {
  uuid: string;
  customer: DuplicateCandidateParty;
  duplicateCustomer: DuplicateCandidateParty;
  status: string;
  rule: string | null;
  evidence: Record<string, unknown> | null;
  reviewedAt: string | null;
  createdAt: string;
}
