# Support CRM — UI Specification Overview

**Date**: 2026-08-27  
**Backend Status**: Frozen (v1), Definition of Done ✓, Performance budgets ✓  
**Audience**: Design tool (Claude Design), implementers  
**Format**: This overview + 13 module-specific files in `docs/ui/{01..13}-*.md`

---

## 1. Three Surfaces, One Backend

The Support CRM backend serves three distinct user-facing applications via the same API:

### 1.1 **Staff/Agent CRM** (`/app`, auth: `bearer` token via `/auth/login`)
- Internal, dense, admin-heavy interface
- ~170 of ~190 API operations
- Roles: Admin, Manager, Supervisor, Agent, Viewer (permission-key-driven)
- Screens: org setup, user/role management, customer 360, ticket workbench, SLA/automation config, knowledge authoring, reporting, integrations
- **App shell**: top nav (logo, user profile, logout), sidebar (module menu), main content, toast/notification corner

### 1.2 **Customer Self-Service Portal** (`/portal`, auth: `portalToken` via `/portal/auth/register` + `/portal/auth/login`)
- Lightweight, customer-focused web app
- Separate identity (email/password, verified account OR unauthenticated guest tracking)
- Screens: register/verify, login, my tickets (list/detail), reply, attachments, feedback survey, account settings
- **App shell**: minimal nav, customer-focused UX
- **Visibility rule**: customers see ONLY their own tickets and public (non-internal) messages; staff internal notes remain hidden

### 1.3 **Public/Anonymous Surfaces** (no auth required)
- Embeddable widgets and publicly accessible pages
- Routes: `/channels/public/web-forms/{formKey}` (intake form), `/channels/public/web-forms/submissions/{trackingToken}` (status tracker), `/public/knowledge/*` (help center), `/channels/chat/sessions` (live chat widget), `/portal/guest/tickets/{token}` (guest tracking)
- **Visibility rule**: anonymous users see only what's explicitly public or shared via token (no account needed, no auth state)

---

## 2. Global Conventions (inherited by every screen)

### 2.1 API Contract Essentials

**Every list screen** supports (and must render UI for):
- **Pagination**: `?page=N&per_page=M` (default 25, max 100 per page) → display current page, total count, page navigator (prev/next or jump-to-page)
- **Sorting**: `?sort=field1,-field2` (comma-separated, `-` prefix for descending) → each column header clickable, visual indicator (↑↓) of sort direction + field
- **Filtering**: `?filter[status]=open&filter[priority]=high&filter[q]=search_text` (deepObject style) → filter panel UI (checkboxes for enums, text input for full-text, date range pickers where applicable)
- **Include**: `?include=related_entity` → opt-in eager-load of nested relationships (e.g., `?include=messages,assignee` on ticket detail to reduce N+1 queries) — not user-facing but affects API perf

**Response envelope**:
```json
{
  "data": [...],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 25,
      "total": 150,
      "last_page": 6
    }
  }
}
```

**Single-resource response** (no pagination):
```json
{
  "data": { /* resource */ }
}
```

**Error response** (all errors follow this shape):
```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The given data was invalid.",
    "request_id": "uuid",
    "field_errors": {
      "email": ["Email is required", "Email must be unique"],
      "password": ["Password must be at least 8 characters"]
    },
    "meta": null
  }
}
```

**Error codes** (→ UI toast/banner behavior):
| Code | HTTP | Meaning | UI Action |
|---|---|---|---|
| `VALIDATION_FAILED` | 422 | Form validation failed | Show error list + field-level callouts |
| `UNAUTHORIZED` | 401 | Missing/invalid token | Redirect to login |
| `FORBIDDEN` | 403 | Insufficient permission | Show "Access Denied" message (rare — most screens hidden instead) |
| `NOT_FOUND` | 404 | Resource doesn't exist (or user unauthorized) | Show "Not Found" page |
| `CONFLICT` | 409 | Resource conflict (e.g., attachment still scanning) | Suggest retry or alternative action |
| `TOO_MANY_REQUESTS` | 429 | Rate limit hit | Show "Please wait" + countdown |
| `INTERNAL_SERVER_ERROR` | 500 | Server error | Show generic error, suggest reload |
| `SERVICE_UNAVAILABLE` | 503 | Service down/maintenance | Show "Service Unavailable" banner |

### 2.2 Bilingual Content (`{ar, en}`) & RTL Support

**Admin-authored fields** (branches, departments, teams, ticket categories, statuses, SLA policies, automation rules, KB articles, quick replies, notification templates, web forms, etc.) are stored bilingual:

**Response shape**:
```json
{
  "name": {
    "ar": "اسم الفرع",
    "en": "Branch Name",
    "__fallback": null | "ar" | "en"
  }
}
```

- `__fallback: "ar"` means the English value was auto-copied from Arabic (user edited only Arabic, system mirrored to EN) → **UI should render a small "Translated from Arabic" badge** on the English field to indicate it's not independently authored.
- **Locale negotiation priority**: (1) `Accept-Language` header from browser, (2) user's `locale` column, (3) org default (currently `'ar'`), (4) `'en'` fallback.
- **Locale selector** in staff/portal account settings; every API request includes language via header: `Accept-Language: ar` or `Accept-Language: en`.

**Form design for bilingual fields**:
- Create/Edit screens with bilingual fields show **two inputs side-by-side** (or tabbed), labeled "العربية / English".
- Implement a `BilingualInput` component (reused across all create/edit forms): toggle fallback badge when one side is edited, optionally allow "auto-copy" button to mirror one locale to the other.

**RTL support**:
- The UI must be **RTL-ready from the start** for Arabic locale. This means:
  - Layout direction flips (flexbox `flex-direction: row-reverse`, grid `grid-auto-flow`, etc.)
  - Nav sidebar moves to right edge
  - Text always left-aligned in LTR, right-aligned in RTL
  - Icon/text pairs flip order (icon-left in LTR → icon-right in RTL)
  - Padding/margin logic preserved (left/right → logical `inline-start`/`inline-end` in CSS)
- **Not yet implemented backend-side**: server does NOT serve RTL directives yet. CSS must handle this via `dir="rtl"` attribute on `<html>` or `[lang="ar"]` selector.

**Server-side message translation**:
- API returns **all error messages and system emails already translated** (e.g., validation errors, business logic errors, SLA breach notifications). Field names and business data are NOT translated (those are user-authored, handled above).

**Client-side copy** (button labels, menu items, help text):
- Owned by the frontend/design tool entirely — this spec does not dictate copy, only structure. Every button and label in the UI spec template should be understood as a placeholder waiting for a final i18n string catalog.

### 2.3 Permission-Key-Driven UI

**Never compare role names** (❌ `if (user.role === 'admin')`) — **always check permission keys** (✓ `if (user.permission_keys.includes('tickets.view.any'))`).

`GET /auth/me` response includes:
```json
{
  "id": "uuid",
  "email": "agent@support.local",
  "name": "Ahmed Ali",
  "locale": "ar",
  "available_locales": ["ar", "en"],
  "permission_keys": [
    "tickets.view.department",
    "tickets.create",
    "tickets.assign",
    "tickets.message.send",
    "customers.view",
    "customers.contact.manage",
    ...
  ]
}
```

**Permission key format**: `{module}.{action}[.{scope}]`  
**Scope convention** (for queue-style views — ticket list, customer list, reports, etc.):
- `own`: only my own data (my tickets, my tasks, my feedback)
- `team`: my team's data
- `department`: my department's data
- `any`: all data across the org

**UI pattern for scope-based filtering**:
```
Ticket Queue:
├─ My Tickets  [shown if: tickets.view.own]
├─ Team Tickets [shown if: tickets.view.team]
├─ Department   [shown if: tickets.view.department]
└─ All Tickets  [shown if: tickets.view.any]
```

**Permission keys by module** (~90 total, full list in `app/Domains/Security/Permissions/PermissionKey.php`):
- **admin**: `roles.manage`, `users.manage`, `users.invite`, `users.activate`, `users.deactivate`, `users.manage_two_factor_policy`, `structure.manage`, `audit.view`
- **org**: `branches.view.any`, `branches.manage.any`, `departments.view.any`, `departments.manage.any`, `teams.view.any`, `teams.manage.any`
- **tickets**: `view.{own|team|department|any}`, `create`, `update`, `assign`, `claim`, `transfer.agent`, `transfer.department`, `reclassify`, `tag`, `status.change`, `reopen`, `spam.mark`, `spam.restore`, `merge`, `split`, `link`, `message.send`, `message.internal_view`, `message.internal_write`, `message.retry`, `categories.manage`, `statuses.manage`, `feedback.view`, `escalate`
- **attachments**: `view`, `download`
- **dataprotection**: `view`, `request_erasure`, `execute_erasure`
- **customers**: `view`, `create`, `update`, `block`, `contact.manage`, `note.view`, `note.create`, `note.delete`, `attachment.manage`, `timeline.view`, `duplicate.view`, `duplicate.review`, `merge`
- **sla**: `policies.view`, `policies.manage`, `reset`
- **automation**: `rules.view`, `rules.manage`, `executions.view`
- **notifications**: `view.own`, `manage_preferences`, `view_delivery_log`
- **workspace**: `tasks.view.{own|others}`, `tasks.manage.own`, `tasks.manage.others`, `quick_replies.view`, `quick_replies.manage`, `ticket.message.mention`, `ticket.watchers.view`
- **channels**: `email.replay.list`, `email.replay.action`, `web_form.view`, `web_form.create`, `web_form.update`, `web_form.delete`, `messaging.templates.view`, `messaging.templates.manage`, `chat.view`, `chat.create`, `chat.accept`, `chat.transfer`, `chat.manage`
- **knowledge**: `articles.view`, `articles.create`, `articles.update`, `articles.publish`, `articles.archive`, `articles.versions.restore`, `categories.manage`
- **ai**: `assistance.use`, `suggestions.resolve`, `usage.view`, `settings.manage`
- **reports**: `view.{own|department|branch|any}`, `export.any`, `schedule.manage`
- **integrations**: `api_tokens.manage`, `webhooks.manage`, `import.manage`

Implement a `<PermissionGate>` component:
```jsx
<PermissionGate requires={['tickets.assign']}>
  <button onClick={assignTicket}>Assign</button>
</PermissionGate>
// If user lacks tickets.assign, button is hidden (not disabled, not shown-but-greyed)
```

### 2.4 Time & SLA Display (Business-Time, Not Wall-Clock)

**Critical rule**: Never calculate countdown/duration client-side using `Date.now()`. All SLA targets, durations, and business-hour math are **computed server-side** and returned in every ticket response.

**Example ticket response** (SLA fields):
```json
{
  "id": "uuid",
  "reference": "TKT-001",
  "sla": {
    "first_response_target": {
      "target_minutes": 480,
      "clock_state": "running" | "paused" | "met" | "breached" | "cancelled",
      "remaining_minutes": 120,
      "remaining_display": "2h 0m",
      "warning_threshold_percent": 80,
      "is_warning": false,
      "is_breach": false,
      "paused_intervals": [...],
      "breach_at_unix": 1693123456,
      "breached_at_unix": null | 1693120000
    },
    "resolution_target": { /* same shape */ }
  }
}
```

**UI rendering for SLA countdowns**:
- Display `remaining_display` (server-formatted, already in user's locale/timezone)
- Show a **SLA Countdown Badge** component (reused everywhere, defined once, used 50+ times):
  - Colors: green (running), yellow (warning, >= threshold%), red (breached)
  - Icon: hourglass (running), pause (paused), checkmark (met), ⚠️ (breach)
  - Text: "1h 30m remaining", "BREACHED", "MET", "PAUSED"
  - Tooltip on hover: full details (policy name, target time, elapsed, etc.)
  - **Never** countdown client-side with `setInterval` — it will drift and users may think it's accurate; instead, cache the initial value and optionally request fresh data on demand (`GET /tickets/{id}`).

### 2.5 Audit Trail (Append-Only, View-Only)

**Every sensitive-data change** (permissions, user deactivation, customer block, ticket status change, SLA policy update, automation rule execution, etc.) is recorded in an audit log. The audit trail is **append-only** — no edit/delete UI ever exists for audit entries.

**Audit Log screen**:
- Read-only list (`GET /audit-logs`)
- Filterable by date range, actor, action, target resource
- Columns: timestamp (UTC + user's branch timezone), actor (user name), action (readable label), target (resource ID + type), before/after JSON (shown on expand), request ID (for tracing)

### 2.6 Attachment Lifecycle & Security

**Attachment states**:
1. **`pending`** — newly uploaded, quarantine scan in progress
   - UI: show spinner/"Scanning…" text on thumbnail
   - Action: disable download button, show "Scanning" reason in tooltip
   - Endpoint: `GET /attachments/{id}` returns 409 Conflict if still pending
2. **`clean`** — scan passed, safe to download
   - UI: show normal thumbnail, enable download button
3. **`infected`** — scan found malware, file deleted from storage
   - UI: show "Blocked — Malware Detected" overlay on thumbnail
   - Action: disable download, show error reason in tooltip
   - Endpoint: `GET /attachments/{id}` returns 422 with `attachment.scan_failed`

**Access control**:
- Unauthorized download returns **404 Not Found** (not 403 Forbidden) — this prevents leaking information about which attachments exist
- UI: on 404, show generic "File not found" message (not "Access denied")

**Upload UX** (multipart form-data to `POST /attachments`):
- Max file size: 10 MB (enforced by config)
- Allowed types: PDF, PNG, JPG, TXT, CSV, DOCX, XLSX (whitelist)
- On upload: show progress bar (multipart chunk tracking), then "Scanning…" state, then success/error
- On error: field-level validation errors in `field_errors.attachment[...]` (e.g., "File too large", "Unsupported file type")

### 2.7 Idempotency & Optimistic UI

**Every write operation** (POST/PATCH that creates or mutates state) requires an `Idempotency-Key` header:
```
POST /tickets
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json
{ "subject": "...", ... }
```

**Behavior**: if the same key is sent twice, the API returns the same response (idempotent). Enables:
- **Retry-safe operations**: if a request times out, client can retry with the same key without double-creating.
- **Optimistic UI**: client generates the key, shows the change immediately, then confirms success (or reverts on error).

**Implementation**: client-side UUID generation (use a crypto library like `nanoid` or `crypto.randomUUID()`), attached to every POST/PATCH by default.

### 2.8 Reusable Components (Used Across Multiple Screens)

Define these components once (in a shared component library), then reuse 50+ times across all screens:

#### `BilingualInput`
Props: `label`, `valueAr`, `valueEn`, `onChange`, `placeholder`, `required`  
Renders two labeled inputs (Arabic/English) side by side; shows `__fallback` badge if one mirrors the other; optional "copy to other" button.

#### `PermissionGate`
Props: `requires` (array of permission keys), `fallback` (optional JSX for denied case)  
Renders children only if user has ALL keys in `requires`; otherwise renders fallback (default: nothing).

#### `SLACountdownBadge`
Props: `sla` (SLA object with clock_state, remaining_display, is_breach, is_warning), `size` ('sm'|'md'|'lg')  
Renders colored badge (green/yellow/red) with icon + text; on hover, tooltip with details.

#### `AttachmentStatusBadge`
Props: `state` ('pending'|'clean'|'infected'), `filename`  
Renders overlay/badge with status, icon, tooltip.

#### `AIsuggestionCard`
Props: `type` ('summary'|'suggested_reply'|'classification'|'suggested_article'|'chatbot_response')`, `content`, `confidence` (0–1), `onAccept`, `onDiscard`, `onLearnMore`  
Renders a card with suggestion, confidence indicator (% bar), Accept/Discard buttons, optional "Learn More" link. Used in ticket detail, ticket reply composer, classification field, chatbot handoff.

#### `DataTable`
Props: `columns` (array of { key, label, sortable, filterable, render })`, `data`, `loading`, `pagination` (current_page, per_page, total, last_page), `onPageChange`, `onSortChange`, `onFilterChange`, `emptyState`  
Renders table with sticky header, column sort indicators, per-column filter buttons, pagination navigator, loading skeleton.

#### `StatusBadge` & `PriorityBadge`
Props: `status`/`priority` (enum value), `size` ('sm'|'md'|'lg')  
Renders colored badge with icon + text (e.g., "Open", "High Priority").

#### `NotificationBell`
Props: `unread_count`, `onBellClick`, `notifications` (array, optionally fetched on demand)  
Renders bell icon with count badge; on click, shows dropdown list of recent notifications (via `GET /notifications`), mark-as-read links, preferences link.

#### `ActivityTimeline` / `TicketHistory`
Props: `events` (array of `TicketEvent`), `loading`  
Renders vertical timeline of events (created, assigned, status changed, message posted, etc.) with timestamps, actor names, icons, descriptions. Expands on click to show full details (before/after JSON for state changes).

---

## 3. App Shells & Navigation

### 3.1 Staff CRM App Shell

```
┌─────────────────────────────────────────────────────┐
│ Logo  |  Search  |  Notifications 🔔  | User 👤 ▼   │  ← Top Nav
├────────────────────────────────────────────────────┤
│ ← Sidebar                    Main Content Area      │
│ ├─ Dashboard                 ┌──────────────────┐   │
│ ├─ Tickets                   │ [Screen Content] │   │
│ │ ├─ Queues                  │ [Loading/Error]  │   │
│ │ └─ All Tickets             │ [Forms/Tables]   │   │
│ ├─ Customers                 │ [Modals]         │   │
│ ├─ Knowledge Base            │ [Toasts]         │   │
│ ├─ Admin                      └──────────────────┘   │
│ │ ├─ Users & Roles                                  │
│ │ ├─ Organization                                   │
│ │ ├─ SLA & Automation                               │
│ │ └─ Integration                                    │
│ └─ Reporting                                        │
└────────────────────────────────────────────────────┘
                      ↑
            [Toast notifications]
```

**Top Nav**:
- **Logo** (left): clickable to go to dashboard
- **Search** (center): global search across tickets, customers, KB (debounced, shows dropdown results)
- **Notifications Bell** (right): shows unread count, dropdown list, settings link
- **User Profile** (far right): name + avatar, dropdown: settings, logout

**Sidebar**:
- Collapsible/expandable (hamburger menu on mobile)
- Module sections (Tickets, Customers, Knowledge, Admin, Reporting, Integrations)
- Active screen highlighted
- Sub-items expand/collapse (Queues under Tickets, Organization under Admin, etc.)
- Conditional rendering based on permission keys (if user lacks `admin.users.manage`, the "Users & Roles" section is hidden entirely)

**Main Content**:
- Breadcrumb trail (Dashboard > Tickets > TKT-001 > Messages)
- Screen heading + optional description/help text
- Forms, tables, or workflow-specific UI
- Modal overlays for secondary actions (create, confirm, etc.)

**Toasts** (bottom-right corner):
- Success (green): "Ticket assigned", auto-dismiss 3s
- Error (red): "Failed to update status", sticky, dismissible
- Warning (yellow): "SLA warning in effect", optional actions
- Info (blue): "Email sent", auto-dismiss 5s

### 3.2 Customer Portal App Shell

```
┌────────────────────────────────────────────────┐
│ Logo  |  My Account 👤 ▼  |  Logout            │  ← Top Nav
├───────────────────────────────────────────────┤
│ Main Content (no sidebar)                      │
│ ┌──────────────────────────────────────────┐  │
│ │ My Tickets List / Ticket Detail / etc.   │  │
│ │ Light, customer-friendly UX              │  │
│ └──────────────────────────────────────────┘  │
└───────────────────────────────────────────────┘
```

**Top Nav**:
- **Logo** (left): back to my-tickets home
- **My Account** (right): settings link, logout
- NO sidebar, NO search, NO admin features

**Main Content**:
- My Tickets list (with simple filter: open/closed), or ticket detail if viewing one
- Reply composer (simple text input, optional attachments)
- Feedback survey (if ticket is closed and feedback hasn't been given yet)
- Settings: email/phone contact info, password change, notification preferences

### 3.3 Public/Anonymous Surfaces (Embeddable)

No fixed app shell — these are self-contained components:

- **Web Form**: embeddable form (can be iframe'd into customer's website), submits to `/channels/public/web-forms/{formKey}/submissions`
- **Status Tracker**: simple lookup page (`/channels/public/web-forms/submissions/{trackingToken}`) — shows ticket reference, subject, status, last update
- **Help Center**: customer-facing knowledge base search + browse (`/public/knowledge/categories` + `/public/knowledge/articles/search`)
- **Live Chat Widget**: embeddable chat bubble + session window

---

## 4. OpenAPI Reference (Source of Truth for Data Schemas)

**Every screen entry** in the module files below will reference exact OpenAPI operation paths. Use these as your single source of truth for:
- Exact field names (e.g., `reference` not `ticket_number`, `is_internal` not `internal_flag`)
- Field types (string, number, boolean, enum, array, object)
- Validation rules (required, min/max length, pattern, enum values)
- Default values
- Read-only vs. editable fields (responses show all data; requests show only mutable fields)

**Documents**:
1. **`docs/api/openapi.yaml`** — living spec (source of truth, updated as features land)
2. **`docs/api/openapi.v1.frozen.yaml`** — v1 baseline snapshot (2026-08-27), byte-identical to above, enforced by `tests/Feature/Api/ContractFreezeTest.php`
3. **`docs/api/collections/support-crm-v1.http`** — golden-path walkthrough (17 chained requests covering the core user journey: login → create ticket → message → assign → status → portal mirror)
4. **`docs/contracts/conventions-digest.md`** — design principles (single-tenant, UUID IDs, bilingual, permission-key-based RBAC, SLA business-time, audit append-only, upload scanning, etc. — already summarized above)
5. **`docs/contracts/localization.md`** — bilingual/i18n architecture (already summarized above)
6. **`docs/contracts/ai-assistance.md`** — AI approval-gate flow and feature toggles (referenced in `09-ai-assistance.md`)
7. **`docs/contracts/automation-rules.md`** — rule condition/action JSON schema (referenced in `05-sla-automation.md`)

**How to use**: for each screen, the "Related endpoints" section lists OpenAPI operation IDs (e.g., `GET /tickets/{id}`, `POST /tickets/{ticket}/messages`). Copy the path into `docs/api/openapi.yaml` (Ctrl+F in your editor or API docs viewer) to pull exact request/response schemas. Do NOT re-derive schemas from the UI spec — always verify against OpenAPI.

---

## 5. Master Screen Inventory

| Module | File | Screens | Surfaces |
|---|---|---|---|
| **Auth & Identity** | `01-auth-identity.md` | Login, 2FA Challenge, Password Reset, Accept Invitation, User List, User Create/Edit (activate/deactivate/2FA toggle), Role List, Role Create/Edit (permission matrix), Audit Log, Data Protection (Erasure/Retention/Backup) | Staff |
| **Organization** | `02-organization.md` | Org Structure (branches/departments/teams tree), Branch Edit (working hours, holidays), User Branch Assignment | Staff |
| **Customers** | `03-customers.md` | Customer List (search, filter), Customer Profile (details, contacts, notes, attachments, timeline), Duplicates Review, Merge Customers, Block Customer | Staff |
| **Tickets** | `04-tickets.md` | Ticket Queues (My/Team/Department/All), Ticket List (paginated, sorted, filtered), Ticket Detail (full view, SLA info, activity), Ticket Reply/Internal Note (composer), Ticket Edit (status, priority, category, assignment), Ticket Merge/Split/Link, Saved Views (create/manage), Ticket Categories (CRUD), Ticket Statuses (CRUD, lifecycle type mapping), Feedback Summary | Staff |
| **SLA & Automation** | `05-sla-automation.md` | SLA Policies (CRUD, policy builder — priority/category/tier matrix), SLA Breach Report, Automation Rules (CRUD, visual rule builder), Rule Execution Log, Escalation Config | Staff |
| **Channels** | `06-channels.md` | Web Form Builder (CRUD, field type picker), Inbound Email Log (replay, classification), Message Template Manager (WhatsApp/SMS bilingual templates), Live Chat Console (queue, accept, transfer, end session) | Staff |
| **Knowledge** | `07-knowledge.md` | KB Article List (search, filter by visibility), Article Editor (bilingual, versioning, publish workflow), Article Feedback (helpful count, ratings), Public Help Center (search, category browse, feedback submission) | Staff + Public |
| **Workspace & Notifications** | `08-workspace-notifications.md` | My Tasks (list, create, state change, reminder), Quick Replies (personal + shared CRUD), Notification Inbox (list, mark read), Notification Preferences (event × channel matrix), Delivery Log | Staff |
| **AI Assistance** | `09-ai-assistance.md` | AI Suggestion Card (reused component), AI Settings (enable/disable per feature, confidence threshold, budget, redaction), Usage Log, Chatbot Handoff Flow | Staff |
| **Reporting** | `10-reporting.md` | Reports List (available report types), Dashboard (KPIs, charts, SLA summary, recent tickets), Report Detail (custom filters, drill-down), Export Modal (format, async progress), Report Schedules (CRUD, recipient list) | Staff |
| **Integrations** | `11-integrations.md` | API Tokens (CRUD, scope checkboxes, copy-once pattern), Webhooks (subscribe to events, delivery log, retry), Bulk Import Wizard (upload → validate → commit), ERP Context Panel (on customer profile) | Staff |
| **Customer Portal** | `12-customer-portal.md` | Register/Verify (email verification), Login, My Tickets (list scoped to owner), Ticket Detail (read + reply), Reply Composer (upload attachments), Feedback Survey, Account Settings (email, password, notification preferences) | Portal |
| **Public Surfaces** | `13-public-surfaces.md` | Web Form Submission, Submission Status Tracker, Public Help Center (search + category browse + article view), Live Chat Widget, Guest Ticket Tracking | Public |

---

## 6. Implementation Guidance

### For Claude Design (or any design tool)
1. **Start with this file** (`00-overview.md`) — read all conventions, components, and app shells.
2. **Pick one module** (e.g., `04-tickets.md`) and read its screens top-to-bottom.
3. **For each screen**, open the related endpoints in `docs/api/openapi.yaml` (use the "Related endpoints" list as your guide).
4. **Mock up the screen** following the template (purpose, permission gates, fields, actions, states, components used).
5. **Move to next module** once one is complete; coordinate with the backend team if you need clarification on any field, enum, or business rule.

### For Frontend Implementers
1. Build component library (BilingualInput, PermissionGate, SLACountdownBadge, etc.) — these are reused 50+ times.
2. Implement a global state management layer (Redux, Zustand, etc.) to hold auth state (user, permission_keys, locale) and API response caching.
3. Set up API client with:
   - Automatic `Idempotency-Key` generation on POST/PATCH
   - Automatic `Accept-Language` header based on user's locale
   - Error handling (map error codes to toast/modal/redirect)
   - Pagination/sorting/filtering query builders
4. Implement two separate auth flows (staff `/auth/login` and portal `/portal/auth/login`) with appropriate guards (PermissionGate for staff, `portalToken` for portal).
5. Build RTL support upfront (CSS Logical Properties, no hardcoded left/right, test with `dir="rtl"`).
6. For SLA countdowns, cache the server-provided value; don't countdown client-side.
7. For attachments, poll status or use WebSocket/Server-Sent Events to detect when scan completes (currently 409 Conflict if still pending).

---

**Next**: Read the module-specific files (`01-auth-identity.md`, `02-organization.md`, ..., `13-public-surfaces.md`) for detailed screen specifications.
