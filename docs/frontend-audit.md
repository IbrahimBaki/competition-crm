# Frontend Production Rebuild Audit

**Audit date:** 2026-08-29  
**Scope:** Read-only repository audit; no application implementation was performed.  
**Source of truth:** Checked-in Laravel, React, runtime configuration, generated client, and tests. Documentation was treated as advisory where it conflicted with code.

## Executive findings

1. The runtime architecture is internally inconsistent.

   - Backend Apache host: `competition-crm.azmsquad.localhost`
   - Built SPA Apache host: `app.support-crm.localhost`
   - Frontend `.env` API: `competition-crm.azmsquad.localhost`
   - Frontend `.env.example` API: `support-crm.localhost`
   - Vite `allowedHosts`: only `competition-crm.azmsquad.localhost`
   - Cookie domain: `.support-crm.localhost`
   - Sanctum stateful origin/CORS: `app.support-crm.localhost`

   The API and SPA do not share a parent domain, so `.support-crm.localhost` cannot authenticate requests to `competition-crm.azmsquad.localhost`.

2. The staff auth flow is cookie-based Sanctum in the implementation, despite UI documentation describing bearer authentication.

3. Portal authentication is bearer-token based, but the frontend transport never attaches the portal token. Portal login, registration, verification, ticket listing, ticket creation, ticket detail, replies, feedback, help center, and guest tracking are not integrated.

4. Generated clients already contain automation, SLA, channel, portal, AI, knowledge, and integration operations. Several placeholder pages incorrectly claim those operations are missing.

5. The frontend exposes 33 routed screens, but only the core staff ticket/customer/organization/security/reporting subset has meaningful integration.

6. Important backend workflows have no frontend routes: knowledge authoring, live chat, public chat, web-form builder/public forms, integration management, AI assistance, notification preferences/delivery failures, audit logs, report schedules, API tokens, imports, webhook subscriptions/deliveries, profile/security, invitations/password reset, public submission tracking, and more.

7. The existing design system is directionally useful but internally conflicted:

   - It calls for glassmorphism while also warning against complex visual effects.
   - Its “Hero + Features + CTA” page model is for marketing pages, not an operations CRM.
   - UI UX Pro Max instead selected a low-decoration flat SaaS/data-product direction.
   - Several on-color pairings in `MASTER.md` need contrast correction, especially black text on orange/blue secondary fills.
   - Existing UI ignores the prescribed Plus Jakarta Sans and semantic tokens.

8. The existing frontend passes automated checks, but that does not establish functional completeness.

   - 27 test files passed.
   - 106 tests passed.
   - TypeScript passed.
   - ESLint passed.
   - Production build passed.
   - Build emits a 711 kB JavaScript chunk warning.
   - Tests emit numerous React `act(...)` warnings and React Router v7 migration warnings.
   - There are no credible portal E2E tests, staff login E2E tests, accessibility tests, responsive tests, or browser/runtime integration tests.

## Canonical local runtime architecture

### Recommended canonical layout

Preserve the real backend hostname already used by Apache and the actual frontend `.env`, and bring the SPA under the same parent domain:

| Concern | Canonical local value |
|---|---|
| Staff/portal SPA | `http://app.competition-crm.azmsquad.localhost` |
| Laravel/API | `http://competition-crm.azmsquad.localhost` |
| API prefix | `/api/v1` |
| `APP_URL` | `http://competition-crm.azmsquad.localhost` |
| `SPA_URL` | `http://app.competition-crm.azmsquad.localhost` |
| `VITE_API_BASE_URL` | `http://competition-crm.azmsquad.localhost` |
| `VITE_API_PREFIX` | `/api/v1` |
| `SESSION_DOMAIN` | `.competition-crm.azmsquad.localhost` |
| `SANCTUM_STATEFUL_DOMAINS` | `app.competition-crm.azmsquad.localhost,app.competition-crm.azmsquad.localhost:5174` as applicable |
| `CORS_ALLOWED_ORIGINS` | Built and Vite SPA origins |
| Vite `allowedHosts` | `app.competition-crm.azmsquad.localhost` |
| Cookie settings | `HttpOnly`, `SameSite=Lax`, `Secure=false` locally; `Secure=true` in HTTPS production |

This is the smallest reconciliation because it preserves the checked-in Laravel vhost and current frontend API target.

An alternative is to rename everything to `support-crm.localhost`, but that requires changing the functioning Laravel vhost and deployed environment identity. The current repository provides less evidence for that choice.

### Required runtime fixes

- Rename the SPA Apache `ServerName`.
- Fix Vite `allowedHosts`; it currently permits the API hostname instead of the Vite SPA hostname.
- Align root `.env.example`, `docker/env-additions.txt`, frontend `.env.example`, README, and Apache configuration.
- Add both built-SPA and Vite development origins to CORS/stateful-domain configuration where necessary.
- Ensure `/sanctum/csrf-cookie` is reachable on the API host.
- Keep staff authentication cookie-based.
- Add a separate portal HTTP client or request policy that attaches `Authorization: Bearer <portal token>` only to portal endpoints.
- Never attach portal tokens to staff endpoints; the backend deliberately rejects that through `portal.deny`.
- Validate actual `Set-Cookie`, preflight, CSRF, login, session bootstrap, logout, 419 retry, and 401 recovery in browser tests.

## Actual identities, roles, and authorization

### User types

| Identity | Backend auth | Authorization |
|---|---|---|
| Staff user | Sanctum stateful session using `web` guard | Permission keys from roles and model policies |
| Portal account | Sanctum personal access token using `portal` guard | Portal account ownership and portal middleware |
| Anonymous visitor | No authentication | Public endpoint throttling, bot protection, tracking/form/session token |

### Seeded staff roles

The actual system role names are:

- `administrator`
- `manager`
- `supervisor`
- `agent`
- `viewer`

Custom roles are also supported. Route availability must therefore be derived from permission keys, not role names.

| Role | Effective product scope |
|---|---|
| Administrator | Every permission |
| Manager | Full organization/security administration; all tickets; customers; SLA/automation; channels; knowledge; branch reports |
| Supervisor | Organization lookup; department/team tickets; customer operations; SLA reset; automation visibility; chat; knowledge; department reports |
| Agent | Own/team ticket operations; customer context/notes; tasks; chat; knowledge authoring; AI assistance; own reports |
| Viewer | Own ticket/message read access, attachment downloads, notifications; no normal mutations |
| Custom role | Exactly its assigned permission keys |

Two correctness issues exist in the frontend permission catalogue:

- It is explicitly a partial copy of the backend catalogue and omits many real keys.
- `/admin/ticket-catalogue` is routed only by `tickets.statuses.manage`, while navigation permits either status or category management. A category-only user can see the link and then receive a forbidden page.

The `/admin` route itself has no permission guard. Its comment claiming that the parent guard prevents access is incorrect.

## Route and screen coverage matrix

Status terms:

- **Integrated:** meaningful backend integration exists, though UI hardening remains.
- **Partial:** some data/actions work, but material workflow gaps exist.
- **Broken/stub:** fake navigation, static content, or no API integration.
- **Obsolete:** not routed.

### Staff and reporting

| Role availability | Route/page | Required data and endpoints | Permissions | Status | Required work |
|---|---|---|---|---|---|
| Any authenticated staff | `/`, `/workspace` — `WorkspacePage` | Mine/department queues, SLA-risk tickets, agent tasks | Component-level queue/task keys | Partial | Correct scope visibility; responsive dashboard; consistent empty/error states |
| Ticket-view scopes | `/tickets` — `TicketsPage` | `GET /tickets`, mine/department queues, statuses, categories, saved views | `tickets.view.*`, queue/action keys | Partial | New-ticket flow; correct scope tabs; complete saved-view CRUD; accessible data grid; bulk-action feedback |
| Ticket-view scopes | `/tickets/:ticketId` — `TicketDetailPage` | Ticket, messages, history, links, watchers, tasks, customer, departments, statuses | View plus per-action permissions | Partial | Missing AI, escalation, merge/split/spam/reopen/link management, delivery events, robust responsive workbench |
| Customer viewers | `/customers` — `CustomersPage` | `GET /customers` | `customers.view` | Partial | Customer creation; denser filtering/sorting/pagination; mobile representation |
| Customer viewers | `/customers/:customerId` — `CustomerDetailPage` | Customer, contacts, notes, attachments, timeline, duplicates, ERP | Customer sub-permissions | Partial | Customer editing, full contact lifecycle UX, scan/download state, merge safety, ERP degraded states |
| Report view scopes | `/reports` — `ReportsIndexPage` | `GET /reports` | `reports.view.*` | Partial | Localized descriptions, query-key standardization, schedule access |
| Report view scopes | `/reports/:reportId` — `ReportDetailPage` | `GET /reports/{key}`, export | View; `reports.export.any` | Partial | Export permission gate; field formatting; stable row keys; empty/error accessibility; export completion/download |
| Report view scopes | `/dashboard` — `ManagementDashboardPage` | Management report | `reports.view.*` | Partial | Replace raw object/JSON rendering; real KPI/chart/table system; remove duplicated shell styling |
| None | `DashboardPage` | No API | None | Obsolete | Delete after confirming no intended route, or replace with canonical workspace/dashboard—not both |

### Administration

| Role availability | Route/page | Required data and endpoints | Permissions | Status | Required work |
|---|---|---|---|---|---|
| Any staff who manually navigates | `/admin` — `AdminIndexPage` | Visible navigation | None | Broken authorization edge | Guard for “any admin permission”; explicit forbidden state |
| Branch viewers | `/admin/branches` — `BranchesPage` | Branch list/create/activate/deactivate | View/manage | Integrated | Unified table/form/dialog primitives and validation summary |
| Branch viewers | `/admin/branches/:branchId` — `BranchDetailPage` | Branch, working hours, holidays | View/manage | Integrated | Stronger calendar UX, timezone guidance, unsaved-change protection |
| Department viewers | `/admin/departments` — `DepartmentsPage` | Department list/create/activation | View/manage | Integrated | Shared table/form system and permission-safe actions |
| Department viewers | `/admin/departments/:departmentId` | Department detail/update/deactivation | View/manage | Integrated | Detail layout and dependency consequences |
| Team viewers | `/admin/teams` — `TeamsPage` | Team list/create/activation | View/manage | Integrated | Shared table/form system |
| Team viewers | `/admin/teams/:teamId` | Team detail/update/deactivation | View/manage | Integrated | Detail layout and dependency consequences |
| User administrators | `/admin/users` — `UsersPage` | Users, invite, lifecycle, placement | User and structure keys | Partial | Invitation state, erase-personal-data, role membership, pagination/filter polish |
| Role administrators | `/admin/roles` — `RolesPage` | Role list/create/delete | `admin.roles.manage` | Integrated | System-role constraints and clearer destructive consequences |
| Role administrators | `/admin/roles/:roleId` | Role, permissions catalogue, users | `admin.roles.manage` | Partial | Membership management and full catalogue presentation |
| Status/category managers | `/admin/ticket-catalogue` | Statuses/categories | Separate manage keys | Partial/broken guard | Split permission gates; CRUD forms; no fabricated priorities panel unless backend supports it |
| SLA viewers | `/admin/sla-policies` | Full generated SLA CRUD exists | View/manage | Stub | Implement list/detail/create/edit/archive/delete and policy validation |
| Automation viewers | `/admin/automation-rules` | Full generated rule CRUD and executions exist | View/manage/executions | Stub with false blocker | Implement rule builder, execution history, testable condition/action UX |
| Channel administrators | `/admin/channels` | Email replay, web forms, messaging templates, chat | Channel-specific keys | Stub with false blocker | Divide into email, web forms, messaging, live chat; implement actual endpoints |
| 2FA-policy administrators | `/admin/settings` | Auth policy | `admin.users.manage_two_factor_policy` | Partial | Add personal 2FA/profile/security separately; field-level validation |

### Portal and public frontend routes

| User | Route/page | Backend endpoints | Status | Required work |
|---|---|---|---|---|
| Anonymous portal visitor | `/portal/login` | `POST /portal/auth/login`, `GET /portal/me` | Broken/fake | Perform login, store token safely, attach bearer token, fetch account, handle errors |
| Anonymous portal visitor | `/portal/register` | `POST /portal/auth/register` | Broken/fake | Match actual fields: email, 12-char password, confirmation, locale; field errors |
| Verification recipient | `/portal/verify` | `POST /portal/auth/verify` | Broken/static | Read token, verify, handle expired/blocked/already-used states |
| Portal account | `/portal/tickets` | `GET /portal/tickets` | Broken/static empty state | Real paginated ticket list |
| Portal account | `/portal/tickets/new` | `POST /portal/tickets` | Broken/fake | Actual category/custom-field/attachment flow |
| Portal account | `/portal/tickets/:id` | Ticket, messages, reply, feedback, attachment download | Broken/static | Full owned-ticket conversation and feedback workflow |
| Anonymous holder of token | `/portal/track/:token` | Guest ticket and feedback | Broken/static | Load ticket, render public-safe history, feedback states |
| Anonymous visitor | `/portal/help` | Public KB categories/articles/search | Broken/static | Search, categories, results, pagination |
| Anonymous visitor | `/portal/help/:slug` | Public article and feedback | Broken/static | Backend identifies articles by route key, not proven slug; reconcile route contract |
| All portal users | `PortalLayout` | Account/logout | Partial visual shell only | Real auth-reactive state, navigation, locale, logout API |

The portal’s `PortalAuthProvider` reads local storage only once and has no reactive login mechanism. Its logout clears local state without calling the backend. The common API mutator does not attach the portal bearer token.

## Backend-supported workflows without a complete frontend

All of these are real checked-in backend capabilities:

- Staff password forgot/reset.
- Invitation acceptance.
- Personal 2FA enable, confirm, disable, and recovery-code regeneration.
- Audit log browsing.
- Data-retention inspection and personal-data erasure.
- Customer creation/update, full contact management, attachment download states.
- Ticket creation, merge, split, spam/restore, reopen, escalation.
- Message delivery-event inspection and retry.
- Ticket saved-view CRUD.
- Quick-reply management.
- SLA policy CRUD and clock reset.
- Automation rule CRUD and execution history.
- Knowledge category/article CRUD, search, preview/render, publish/archive, version restore.
- AI summaries, suggested replies, classification, article suggestions, suggestion resolution, usage.
- Notification preferences and failed delivery logs.
- Report schedule CRUD.
- Email replay administration.
- Web-form builder and public form rendering/submission/tracking.
- Staff and public live chat flows.
- Messaging template browsing.
- API token management.
- Webhook subscription and delivery management.
- Import run creation/status/error reporting.
- Public knowledge feedback.
- Public guest feedback.
- Public chat and web-form embedding surfaces.
- Portal profile/account view and attachment download.

These require explicit routes/screens or intentional integration into existing workbench pages.

## Functional and API audit

### Critical defects

- Portal bearer authentication is not transported.
- Host/cookie configuration prevents reliable staff session auth.
- Portal screens perform fake navigation and can appear successful without any backend state.
- `PortalProtectedRoute` trusts local storage rather than validating `/portal/me`.
- `AuthProvider` turns non-401 bootstrap failures into “unauthenticated,” hiding outages as login failures.
- Staff 401 recovery applies globally and is not designed for portal token failures.
- `EXEMPT_FROM_RECOVERY` matches URL substrings, so portal auth endpoints can be classified incorrectly.
- Write requests do not automatically receive the documented required `Idempotency-Key`.
- CSRF/bootstrap transport is hand-written while the rest is generated, creating two auth paths.
- Multiple screens cast generated responses through `unknown`, weakening the OpenAPI contract.
- Some custom query keys do not match generated keys. The most obvious is agent-task invalidation using `['/tickets', id]`, which cannot invalidate the generated ticket query.
- Report queries use ad hoc keys while generated query-key factories already exist.
- The generated client is not the blocker claimed by the SLA, automation, and channel pages.

### Validation and state handling

- API normalization supports `field_errors`, but most forms only show a generic message.
- There is no shared focusable validation summary.
- Several forms use placeholders instead of visible labels.
- There is no consistent toast/success system.
- Destructive confirmation exists in isolated components, not as a complete application policy.
- Modal implementations are mostly custom fixed-position `<div>` elements without proven focus trap, initial focus, Escape handling, or focus return.
- Loading/empty/error components exist, but coverage is inconsistent and largely generic.
- Portal pages have almost none of these states.
- Upload UX lacks a complete scan-state lifecycle and download handling.
- Report export is fire-and-forget because no completion/download route is exposed; the UI must state this honestly until the backend contract is completed.

### Routing problems

- `DashboardPage` is unused.
- `/admin` lacks the guard implied by its comments.
- Ticket catalogue navigation and route permission predicates disagree.
- Staff and portal are bundled into one large synchronous route graph.
- No route-level code splitting; this contributes to the 711 kB bundle.
- Missing password reset, invitation acceptance, profile/security, knowledge, integrations, audit, notifications settings, channel sub-pages, and public widget routes.
- Portal help uses `:slug`, while the backend route is `{article}`; route-key behavior must be confirmed.

### OpenAPI status

The checked-in generated client broadly reflects the current OpenAPI file and includes the allegedly missing modules. However:

- Documentation still describes an older output shape (`endpoints.ts`) while Orval is configured for tag-split output.
- OpenAPI declares staff bearer security even though the implementation uses stateful Sanctum cookies.
- OpenAPI calls bearer format “JWT,” while Laravel returns Sanctum personal-access tokens for portal accounts.
- The frontend does not honor separate security schemes.
- Generated generic collection models force many manual wire adapters and unsafe casts.
- A clean regeneration/diff should be a mandatory checkpoint before feature implementation.

## UI/UX audit and rebrand direction

UI UX Pro Max favors a flat, low-decoration SaaS/data-product system for this application. That is a better fit than applying glassmorphism throughout dense operational screens.

### Product direction

- Light-first operations interface.
- Trust-oriented blue as the main navigation/action color.
- Orange reserved for a small number of high-attention actions; danger remains red.
- Flat surfaces with borders and restrained elevation.
- Plus Jakarta Sans, with tabular numerals for SLA, dates, counts, and report values.
- One consistent SVG icon family; remove emoji navigation icons.
- Semantic tokens rather than raw Tailwind gray/blue values in pages.
- Density modes for large tables, while retaining 44 px touch targets.
- Arabic and English treated as equal product modes, using logical CSS properties.

### Information architecture

Staff shell:

- Workspace
- Tickets
- Customers
- Knowledge
- Channels
- Reports
- Administration
  - Organization
  - People & access
  - Ticket configuration
  - SLA & automation
  - Integrations
  - Security & audit
- Account

The shell should be responsive:

- Desktop: persistent collapsible sidebar.
- Tablet: compact rail or overlay navigation.
- Mobile: top bar plus modal drawer; tables become cards or controlled horizontal data regions.
- Deep pages get breadcrumbs and stable back behavior.
- Global search should be added only once backed by real endpoints; no fake omnibox.

Portal shell:

- Help center
- My tickets
- New request
- Account/language
- Sign in/out

### Core component system

Standardize:

- `AppShell`, `PortalShell`, `PageHeader`, `Breadcrumbs`
- `Button`, `IconButton`, `LinkButton`
- `Field`, `Input`, `Select`, `Textarea`, `BilingualField`
- `FormErrorSummary`, inline error messages
- `DataTable`, responsive list cards, pagination, filter bar, saved views
- `StatusBadge`, `PriorityBadge`, `SlaBadge`, attachment state
- `Dialog`, `AlertDialog`, `Drawer`, `Popover`, `Tooltip`
- `ToastProvider`
- `Skeleton`, `EmptyState`, `ErrorState`, `ForbiddenState`, `OfflineState`
- `PermissionGate`
- `CustomerSummary`, `TicketSummary`, activity timeline
- `ConfirmTypedAction` for especially destructive actions

Accessibility requirements include visible focus, route-change focus management, skip link, semantic landmarks, `aria-sort`, field-error association, keyboard-operable dialogs, reduced motion, non-color-only states, and accessible authentication that allows paste/password managers.

## Recommended frontend architecture

Keep React, React Router, TanStack Query, Orval, Axios, i18next, Tailwind, and strict TypeScript. They are workable and do not justify a rewrite.

Standardize by feature:

```text
src/
  app/
    providers/
    router/
    shells/
  api/
    generated/
    clients/
      staffClient.ts
      portalClient.ts
    errors/
    queryKeys/
  components/
    ui/
    forms/
    data/
    feedback/
  features/
    auth/
    workspace/
    tickets/
    customers/
    knowledge/
    channels/
    reports/
    administration/
    integrations/
    portal/
  pages/
  styles/
    tokens.css
```

Key rules:

- Generated functions are the transport contract.
- Staff and portal clients share normalization but have separate auth policies.
- Generated query-key factories are mandatory.
- Mutations declare invalidation centrally by resource.
- Forms use a common server-error adapter.
- Permission checks happen at navigation, route, and action levels; backend remains authoritative.
- Page components orchestrate features but do not contain raw transport logic.
- Lazy-load by route/module.
- Avoid introducing a second global state library unless a proven need appears; auth, locale, router state, and TanStack Query are sufficient.

## Checkpointed implementation plan

Each checkpoint is independently verifiable.

### 1. Contract and runtime baseline

Files:

- `docker/vhost-competition-crm.conf`
- `docker/env-additions.txt`
- `.env.example`
- `frontend/.env.example`
- `frontend/vite.config.ts`
- `config/cors.php`
- `config/session.php`
- `config/sanctum.php`
- `README.md`
- OpenAPI security declarations

Work:

- Apply the canonical hosts above.
- Document built and Vite development modes.
- Reconcile cookie, CORS, CSRF, stateful domains, staff session auth, and portal bearer auth.
- Regenerate the API client and review the diff.

Verification:

```bash
cd frontend && npm run api:generate && npm run typecheck
docker exec -w /var/www/html/competition-crm azm-php82 php artisan route:list
curl -i http://competition-crm.azmsquad.localhost/sanctum/csrf-cookie
```

### 2. Design foundations and shared interaction primitives

Files:

- `frontend/src/index.css`
- Tailwind configuration
- New `styles/tokens.css`
- New shared UI/form/data/dialog/toast components
- Shell state components

Pages affected: every page.

Work:

- Implement semantic tokens, typography, spacing, responsive breakpoints, focus and motion rules.
- Implement accessible dialogs, forms, tables, states, pagination, and toasts.
- Add component-level tests.

Verification:

```bash
cd frontend && npm run test && npm run typecheck && npm run lint
```

Plus browser checks at 375, 768, 1024, and 1440 px.

### 3. Authentication, account, and route security

Pages:

- `/login`
- `/login/two-factor`
- New forgot-password page
- New reset-password page
- New invitation-acceptance page
- New staff profile/security page
- `/portal/login`
- `/portal/register`
- `/portal/verify`

APIs:

- Staff auth/me/logout/password/2FA/invitations
- Portal auth/login/register/verify/logout/me

Files:

- `auth/*`
- `portal/auth/*`
- API clients/mutator
- router
- auth pages

Expected behavior:

- Correct redirect preservation, validation, 2FA continuation, session expiry, portal token validation, separate logout, account security.

Verification:

```bash
cd frontend && npm run test && npm run typecheck
```

Browser E2E: staff login/logout/expiry/2FA and portal register/verify/login/logout.

### 4. Responsive application shells and navigation

Pages: all staff and portal routes.

Files:

- `shell/AppLayout.tsx`
- `shell/Sidebar.tsx`
- `shell/TopBar.tsx`
- Navigation configuration
- `portal/shell/PortalLayout.tsx`

Work:

- Permission-derived navigation, mobile drawer, breadcrumbs, skip link, route focus, locale, notification access, account menu.
- Remove emojis and obsolete `DashboardPage`.

Verification:

```bash
cd frontend && npm run test && npm run lint && npm run build
```

### 5. Workspace and notifications

Pages:

- `/`
- `/workspace`
- New notification preferences page
- New failed-delivery administration page

APIs:

- Ticket queues
- Agent tasks
- Notifications/read/read-all/preferences/failed deliveries

Expected behavior:

- Correct scope panels, task CRUD/state/rescheduling, notification routing, preferences, loading/empty/error behavior.

Verification:

```bash
cd frontend && npm run test -- workspace
```

Plus role-based browser tests.

### 6. Ticket list and creation

Pages:

- `/tickets`
- New `/tickets/new`

APIs:

- Tickets, queues, statuses, categories, saved views, departments

Work:

- Scope-aware queue tabs, URL-backed filters, sorting, pagination, accessible selection/bulk actions, ticket creation and custom fields.

Verification:

```bash
cd frontend && npm run test -- tickets
```

### 7. Ticket workbench

Page:

- `/tickets/:ticketId`

APIs:

- Ticket, messages, delivery events/retry, status, reopen, spam/restore, merge, split, assignment, transfer, claim, links, watchers, history, SLA clock/reset, escalation, tasks, attachments

Expected behavior:

- Complete workbench without exposing unauthorized actions.
- Conflict handling and draft preservation.
- Tablet/mobile layout.

Verification:

```bash
cd frontend && npm run test -- tickets
```

E2E for every mutation above.

### 8. AI and knowledge assistance inside tickets

Pages:

- `/tickets/:ticketId`
- New `/knowledge`
- New `/knowledge/new`
- New `/knowledge/:articleId`
- New `/knowledge/:articleId/edit`
- New `/admin/knowledge/categories`
- New `/admin/ai`

APIs:

- AI assist/suggestions/usage
- Knowledge categories/articles/search/render/state/versions/restore

Verification:

```bash
cd frontend && npm run test -- knowledge ai
```

### 9. Customer CRM

Pages:

- `/customers`
- New `/customers/new`
- `/customers/:customerId`

APIs:

- Customers CRUD, contacts, notes, attachments, timeline, duplicates, merge, block/unblock, ERP context

Expected behavior:

- Complete customer lifecycle, scan-state handling, safe merge/block confirmations, real creation/editing.

Verification:

```bash
cd frontend && npm run test -- customers
```

### 10. Organization administration

Pages:

- `/admin`
- `/admin/branches`
- `/admin/branches/:branchId`
- `/admin/departments`
- `/admin/departments/:departmentId`
- `/admin/teams`
- `/admin/teams/:teamId`

APIs:

- Branch/department/team CRUD and activation
- Working hours and holidays

Verification:

```bash
cd frontend && npm run test -- organisation
```

### 11. People, roles, account policy, audit, and retention

Pages:

- `/admin/users`
- `/admin/roles`
- `/admin/roles/:roleId`
- `/admin/settings`
- New `/admin/audit`
- New `/admin/data-protection`

APIs:

- Users/invite/lifecycle/placement
- Roles/membership/catalogue
- Auth policy
- Audit logs
- Retention and erasure

Verification:

```bash
cd frontend && npm run test -- security admin
```

### 12. Ticket catalogue, SLA, and automation

Pages:

- `/admin/ticket-catalogue`
- `/admin/sla-policies`
- New `/admin/sla-policies/new`
- New `/admin/sla-policies/:policyId`
- `/admin/automation-rules`
- New `/admin/automation-rules/new`
- New `/admin/automation-rules/:ruleId`
- New `/admin/automation-executions`

APIs:

- Status/category management
- SLA policy CRUD
- Automation rule CRUD/executions

Verification:

```bash
cd frontend && npm run test -- sla automation catalogue
```

### 13. Channels and public intake surfaces

Pages:

- `/admin/channels`
- New `/admin/channels/email`
- New `/admin/channels/web-forms`
- New `/admin/channels/web-forms/:formId`
- New `/admin/channels/messaging`
- New `/admin/channels/chat`
- New `/forms/:formKey`
- New `/forms/submissions/:trackingToken`
- New `/chat`

APIs:

- Email replay
- Web-form CRUD/public submission/status
- Messaging templates
- Staff/public chat

Verification:

```bash
cd frontend && npm run test -- channels
```

E2E for public submission and chat isolation.

### 14. Reports and schedules

Pages:

- `/reports`
- `/reports/:reportId`
- `/dashboard`
- New `/report-schedules`
- New `/report-schedules/new`
- New `/report-schedules/:scheduleId`

APIs:

- Report definitions/results/export
- Schedule CRUD

Work:

- Typed field formatting, stable keys, accessible tables/charts, permission-gated exports.
- Resolve export completion/download contract before claiming completed exports.

Verification:

```bash
cd frontend && npm run test -- reports
```

### 15. Integrations

Pages:

- New `/admin/integrations`
- New `/admin/integrations/api-tokens`
- New `/admin/integrations/webhooks`
- New `/admin/integrations/webhooks/:subscriptionId`
- New `/admin/integrations/imports`
- New `/admin/integrations/imports/:runId`

APIs:

- API tokens
- Webhook subscriptions/deliveries
- Import runs

Verification:

```bash
cd frontend && npm run test -- integrations
```

### 16. Customer portal and public help center

Pages:

- `/portal/tickets`
- `/portal/tickets/new`
- `/portal/tickets/:id`
- `/portal/track/:token`
- `/portal/help`
- `/portal/help/:articleRouteKey`
- New `/portal/account`

APIs:

- Portal ticket list/create/detail/messages/feedback/attachments
- Guest tracking/feedback
- Public knowledge/categories/search/article/feedback
- Portal account

Verification:

```bash
cd frontend && npm run test -- portal
```

E2E proving portal/staff isolation and internal-note exclusion.

### 17. Final hardening and release proof

Files: frontend and runtime configuration globally.

Work:

- Route-level splitting.
- Remove unsafe casts and ad hoc query keys.
- Resolve test warnings.
- Accessibility scan.
- Console/network-error review.
- Production runtime smoke test.
- Confirm no TODO, fake navigation, static mock data, or placeholder controls remain.

Verification:

```bash
cd frontend
npm run api:generate
npm run test
npm run typecheck
npm run lint
npm run build

docker exec -w /var/www/html/competition-crm azm-php82 vendor/bin/pint --test
docker exec -w /var/www/html/competition-crm azm-php82 vendor/bin/phpstan analyse --no-progress
docker exec -w /var/www/html/competition-crm azm-php82 vendor/bin/pest
```

## Release acceptance checklist

Release is acceptable only when all are demonstrably true:

- [ ] Every routed page above has complete production UI.
- [ ] Every implemented backend user workflow is reachable or explicitly documented as API-only.
- [ ] Staff, portal, and anonymous identities remain isolated.
- [ ] Administrator, manager, supervisor, agent, viewer, and custom roles are verified using permissions—not role-name UI checks.
- [ ] Navigation, routes, actions, and backend policies agree.
- [ ] Staff login, logout, recovery, expiry, 2FA, reset, and invitation flows work.
- [ ] Portal registration, verification, login, token validation, logout, and recovery states work.
- [ ] All queries use canonical generated query keys.
- [ ] All mutations invalidate or update the correct resources.
- [ ] Required idempotency keys are attached.
- [ ] Server validation appears inline and in an accessible error summary.
- [ ] Destructive actions explain consequences and require confirmation.
- [ ] List sorting/filtering/search/pagination works where backend-supported.
- [ ] Every async surface has loading, empty, error, retry, and forbidden behavior as applicable.
- [ ] Desktop, tablet, mobile portrait, and mobile landscape are verified.
- [ ] Keyboard navigation, focus trapping/return, route focus, skip navigation, and visible focus work.
- [ ] Contrast and non-color-only communication meet WCAG AA.
- [ ] Arabic RTL and English LTR are verified end-to-end.
- [ ] Reduced-motion mode works.
- [ ] No important console errors, unhandled rejections, or recurring React warnings remain.
- [ ] TypeScript, lint, tests, and production build pass.
- [ ] Bundle-size warning is addressed through route/module splitting.
- [ ] Canonical local hostnames, cookies, CORS, CSRF, Sanctum, Apache, Docker, and environment examples agree.
- [ ] Generated client matches the reviewed OpenAPI specification.
- [ ] No fake success paths, placeholder pages, “coming soon,” fabricated data, or dead controls remain.

The rebuild should begin with the runtime/authentication checkpoint. Until the domain and portal transport problems are fixed, UI work cannot be reliably verified end-to-end.
