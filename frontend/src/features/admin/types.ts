// Local domain types for the admin feature slice — hand-written, not the
// generated OpenAPI client types (which are drifted/generic for most admin
// endpoints; see api/wire.ts doc comments per resource). Keep in sync with:
//   app/Domains/Organisation/Http/Resources/{Branch,Department,Team,BranchWorkingHour,BranchHoliday}Resource.php
//   app/Domains/Security/Http/Resources/{User,Invitation,Role}Resource.php
//   app/Domains/Ticketing/Http/Resources/{TicketStatus,TicketCategory}Resource.php
//   app/Domains/Channels/**/Http/Resources/*
//
// Every bilingual label is `{ ar, en }`, matching
// frontend/src/api/generated/model/bilingualString.ts.
export interface BilingualText {
  ar: string;
  en: string;
}

// --- Organisation ----------------------------------------------------------

/**
 * `BranchResource::toArray()` flattens `name` to a single locale-resolved
 * string via `$this->name->forLocale(App::getLocale())` — it does NOT return
 * `{ar, en}` on read, even though create/update require both. See
 * .squad/gaps/36-483.md #1: the edit form can only pre-fill the locale
 * matching the viewer's active UI language; the other locale must be
 * re-entered.
 */
export interface Branch {
  id: string;
  name: string;
  code: string;
  timezone: string;
  is24x7: boolean;
  isActive: boolean;
  createdAt: string | null;
  updatedAt: string | null;
}

export interface Department {
  id: string;
  branchId: string;
  name: string;
  code: string;
  isActive: boolean;
  createdAt: string | null;
  updatedAt: string | null;
}

export interface Team {
  id: string;
  departmentId: string;
  name: string;
  code: string;
  isActive: boolean;
  createdAt: string | null;
  updatedAt: string | null;
}

export interface BranchWorkingHour {
  dayOfWeek: number; // 0-6, matches PHP `Illuminate\Support\Carbon` dayOfWeek (0 = Sunday)
  isWorking: boolean;
  opensAt: string | null; // "H:i", e.g. "09:00"
  closesAt: string | null;
}

export interface BranchHoliday {
  id: string;
  name: string;
  date: string | null; // "Y-m-d", mutually exclusive with recurringMonthDay
  recurringMonthDay: string | null; // "MM-DD"
  createdAt: string | null;
  updatedAt: string | null;
}

// --- Security: users, roles, permissions ------------------------------------

/**
 * `UserLifecycleController::index()` paginates raw `User::query()` (not
 * wrapped in `UserResource`, same "raw Eloquent columns" pattern as
 * `CustomerController::index()` — see frontend/src/features/customers/types.ts).
 * There is no "get one user" route and `UserResource` itself carries no
 * `roles`/branch/department fields — see .squad/gaps/36-483.md #5:
 * the Users list can show identity + active/deactivated status only; a
 * user's current roles and branch/department placement are not readable
 * from any endpoint, so `UserPlacementPanel` and role assignment are
 * write-only actions (attach/detach), not a reflect-current-state editor.
 */
export type AdminUserStatus = 'active' | 'deactivated';

export interface AdminUser {
  id: string;
  name: string;
  email: string;
  status: AdminUserStatus;
  createdAt: string | null;
}

/** `InvitationResource`: `{id, email, expires_at, accepted_at, created_at}` — no status enum. */
export interface Invitation {
  id: string;
  email: string;
  expiresAt: string | null;
  acceptedAt: string | null;
  createdAt: string | null;
}

/**
 * `RoleResource`: `name` is a unique slug set once on create and never
 * updatable (not in `UpdateRoleRequest::rules()`); `display_name` is the
 * bilingual label shown/edited in the UI. `RolePolicy::delete` denies any
 * `is_system` role; there is no separate "administrator" flag — the
 * administrator role is identified server-side by `Role::ADMINISTRATOR`
 * name and enforces its own admin-key guard as an inline 422
 * (`admin_role_locked`) on save, not a pre-emptive read-only lock. See
 * .squad/gaps/36-483.md #6.
 */
export interface Role {
  id: string;
  name: string;
  displayName: BilingualText;
  isSystem: boolean;
  permissionKeys: string[];
  createdAt: string | null;
  updatedAt: string | null;
}

/**
 * `PermissionCatalogueController::show()` groups permission keys by module
 * and returns NO description field — only `key`/`action`/`scope`. The plan's
 * assumed "plain-language label/description" does not exist on the backend;
 * see .squad/gaps/36-483.md #2. `RoleEditor` renders the raw key under a
 * translated module heading (the documented fallback the plan itself
 * allows for this exact case).
 */
export interface PermissionCatalogueEntry {
  key: string;
  action: string;
  scope: string | null;
}

export interface PermissionCatalogueGroup {
  module: string;
  entries: PermissionCatalogueEntry[];
}

// --- Ticket catalogue --------------------------------------------------------

export type TicketStatusLifecycleType = string;

export interface TicketStatusDefinition {
  id: string;
  key: string;
  name: string;
  lifecycleType: TicketStatusLifecycleType;
  isDefault: boolean;
  isSystem: boolean;
  isActive: boolean;
  position: number;
  stopsSlaClock: boolean;
}

/**
 * `TicketCategoryResource` — unlike Branch/Department/Team, `name` is NOT
 * flattened by `forLocale()`; it serializes as `{ar, en}` directly via
 * `BilingualStringCast`, so (unlike organisation forms) edit can pre-fill
 * both locales from one GET. `fields` is only populated `whenLoaded` (the
 * index endpoint eager-loads it) — see TicketCategoryField below; there is
 * no CRUD endpoint for it (.squad/gaps/36-483.md #7), so it is read-only.
 */
export interface TicketCategory {
  id: string;
  code: string;
  parentId: string | null;
  name: BilingualText;
  depth: number;
  isActive: boolean;
  fields: TicketCategoryField[];
}

export type TicketCategoryFieldType = 'text' | 'number' | 'date' | 'select' | 'boolean';

export interface TicketCategoryField {
  id: string;
  key: string;
  label: BilingualText;
  type: TicketCategoryFieldType;
  isRequired: boolean;
  options: string[];
  position: number;
}

export type TicketPriority = 'low' | 'normal' | 'high' | 'urgent';

// --- SLA ----------------------------------------------------------------
// No generated client exists for this domain (spec drift — see
// .squad/gaps/36-483.md #3) and the backend SLA policy controller is
// fatally broken (missing SlaPolicyResource class — see #4, not fixed here
// per this story's "no backend changes" rule). Types below mirror the
// FormRequest-validated surface only, not the full model, since the
// request classes silently accept more fields on the model than they
// validate.

export type SlaTargetType = 'first_response' | 'resolution';

export interface SlaTarget {
  type: SlaTargetType;
  minutes: number;
}

export interface SlaPolicy {
  uuid: string;
  name: BilingualText;
  targets: SlaTarget[];
}

// --- Automation -----------------------------------------------------------
// No generated client exists for automation-rule CRUD (only read hooks are
// generated) — see .squad/gaps/36-483.md #3. Mutations are hand-written
// against `apiRequest` in api/wire.ts.

export type RuleTrigger =
  | 'ticket_created'
  | 'ticket_updated'
  | 'status_changed'
  | 'priority_changed'
  | 'department_transferred'
  | 'message_posted'
  | 'sla_warning_raised'
  | 'sla_breached'
  | 'scheduled'
  | 'manual_escalation';

export type RuleActionType =
  | 'assign'
  | 'reassign'
  | 'transfer_department'
  | 'raise_priority'
  | 'change_status'
  | 'add_tag'
  | 'notify'
  | 'escalate';

export interface AutomationRuleAction {
  type: RuleActionType;
  [key: string]: unknown;
}

export interface AutomationRuleCondition {
  [key: string]: unknown;
}

export interface AutomationRule {
  uuid: string;
  key: string;
  name: BilingualText;
  trigger: RuleTrigger;
  isActive: boolean;
  priority: number;
  stopOnMatch: boolean;
  conditions: AutomationRuleCondition[];
  actions: AutomationRuleAction[];
  escalationLevel: number | null;
  cooldownMinutes: number | null;
  createdAt: string | null;
  updatedAt: string | null;
}

export type AutomationRuleExecutionOutcome = 'matched' | 'skipped' | 'failed';

export interface AutomationRuleExecution {
  uuid: string;
  ruleId: string;
  ticketId: string | null;
  trigger: string;
  outcome: AutomationRuleExecutionOutcome;
  reason: string | null;
  executedAt: string | null;
}

// --- Channels ---------------------------------------------------------------
// See .squad/gaps/36-483.md #8-9: both the inbound-email list and replay
// endpoints are almost certainly fatal server bugs (a static call to an
// instance method, and a call to a nonexistent `ApiResponse::success()`) —
// built to the intended contract below, not fixed (out of scope).

export type InboundEmailState = 'received' | 'processed' | 'suppressed' | 'failed';

export interface InboundEmailMessage {
  id: string;
  fromAddress: string | null;
  subject: string | null;
  state: InboundEmailState;
  attempts: number;
  lastError: string | null;
  receivedAt: string | null;
}

/** `ProviderMessageTemplateResource` — approval state is presence/absence of `approvedAt`, not an enum field. */
export interface MessagingTemplate {
  uuid: string;
  channel: string;
  key: string;
  providerTemplateName: string;
  body: BilingualText;
  isActive: boolean;
  approvedAt: string | null;
}

export interface WebFormField {
  id: string;
  key: string;
  type: string;
  isRequired: boolean;
  label: BilingualText;
}

export interface WebForm {
  id: string;
  key: string;
  title: BilingualText;
  departmentId: string;
  ticketCategoryId: string | null;
  defaultPriority: TicketPriority;
  isActive: boolean;
  fields: WebFormField[];
}
