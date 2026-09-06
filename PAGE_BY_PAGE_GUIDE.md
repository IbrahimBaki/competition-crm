# Page-by-page guide

## How to read this inventory

“V2” identifies a rebuilt surface, “shared/operations” a route still reachable in the mixed migration. Routes below are the meaningful React routes from `frontend/src/router.tsx`; API support refers to `/api/v1` routes in `routes/api.php`. A staff route additionally requires authentication even where the table names a permission.

## Staff authentication and workspace

| Route / component / surface | Access and business purpose | User sees, does, and downstream result | API/evidence |
|---|---|---|---|
| `/login` `LoginPage` shared | unauthenticated staff | email/password; starts staff session or sends user to 2FA | `POST /auth/login` |
| `/login/two-factor` `TwoFactorPage` shared | pending staff 2FA | authenticator/recovery code; finishes session | `POST /auth/two-factor/challenge` |
| `/forgot-password`, `/reset-password` `RecoveryPages` | unauthenticated staff | request and complete reset; reset link/token must arrive through configured mail | auth password endpoints |
| `/invitations/:token` `InvitationAcceptancePage` | invited user | establishes password and activates invitation | invitation accept endpoint |
| `/` and `/workspace` `WorkspacePage` V2 | authenticated; cards conditional on permissions | attention counts, My Work, department queue, overdue tasks, SLA risk and notifications. Start here to choose work. Create/complete task affects assigned user; notification target may link to ticket. | ticket queue, tasks, notifications hooks; `WorkspaceV2.tsx` |
| `/account` `StaffAccountPage` shared | any staff | identity/permission count and 2FA set-up, confirm, recovery codes, disable. | 2FA endpoints |

## Tickets and customers

| Route / component / surface | Access and business purpose | User sees, does, and downstream result | API/evidence |
|---|---|---|---|
| `/tickets` `TicketsPage` V2 | any ticket-view scope | all/mine/department queue, URL-backed search/filter/sort/pagination, saved views, selection/bulk assignment/status. Open a record; queue/filter can hide changed ticket. | GET tickets/queues; PATCH/assign/status in `useTicket*` |
| `/tickets/new` `NewTicketPage` modal | `tickets.create` | create staff-originated ticket with customer, department/category/priority/custom fields. It begins `new`, receives default status/SLA if configured. | POST `/tickets`; `CreateTicket.php` |
| `/tickets/:ticketId` `TicketDetailPage` V2 workbench | ticket-view scope plus record scope | conversation and public/internal composer; status/properties/SLA; assignment; customer context; watchers; ticket tasks; history; AI panel. Public reply reaches eligible customer channel; internal note does not. Assignment/task/mention/watch creates downstream staff work/notifications. | ticket/messages/history/watchers/tasks/SLA/AI endpoints |
| `/customers` `CustomersPage` shared | `customers.view` | search/list and create entry point. Customer list is the identity lookup before work. | customers list hooks |
| `/customers/new` `NewCustomerPage` modal | `customers.create` | create customer record; duplicate detector may produce candidate for review. | POST customers |
| `/customers/:customerId` `CustomerDetailPage` shared | `customers.view` | identity/header, status, notes, files, timeline, ERP context, duplicate/merge and dangerous actions according to permissions. Customer changes feed ticket/portal association; merge moves linked records. | customer subresource endpoints |

## Knowledge, reports and operational pages

| Route / component / surface | Access and business purpose | User sees, does, and downstream result | API/evidence |
|---|---|---|---|
| `/knowledge` `KnowledgePage` V2 | article view | searchable/sortable article ledger; category management if allowed; create entry point. | knowledge articles/categories |
| `/knowledge/new`, `/:articleId/edit` `CreationPages` modal | create/update | author content/visibility/category; lifecycle controls on detail govern publication. | POST/PATCH knowledge article |
| `/knowledge/:articleId` `KnowledgeArticlePage` shared | article view | article details, rendered preview when published, lifecycle transitions and version restore if permitted. Published public content appears in help search. | versions/state/render endpoints |
| `/reports` `ReportsIndexPage` | report view scope | report catalogue; choose report. | GET reports |
| `/reports/:reportId` `ReportDetailPage` | report view scope | filters (in URL), result and export action. Export is asynchronous. | report show/export |
| `/dashboard` `ManagementDashboardPage` | report view scope | management summary based on accessible report data. | reports hooks |
| `/report-schedules` `ReportSchedulesPage` shared | schedule manage | CRUD recurring report deliveries: report key, filters JSON, frequency/time zone/recipients. Scheduler is required. | report schedules CRUD |
| `/admin/audit` `AuditLogPage` | audit view | immutable audit ledger; inspect actor/action/target. | GET audit logs |
| `/admin/data-protection` `DataProtectionPage` | retention view | configured retention classes/cutoffs; this screen does not execute erasure. | GET retention |
| `/admin/integrations` `IntegrationsPage` | any integration manage | API tokens (secret shown once), webhook subscriptions/deliveries, import runs. Needs real external endpoints/files. | integration/token/webhook/import endpoints |
| `/admin/ai` `AiOperationsPage` | AI usage or setting manage | pending AI suggestions accept/discard and usage ledger. Acceptance is human control, not automatic sending. | AI suggestions/usage |

## Organisation and administration

| Route / component | Guard | Product-owner explanation and main action | API |
|---|---|---|---|
| `/admin` `AdminIndexPage` | authenticated only (but child capabilities vary) | admin landing; use it to find configuration module. | n/a |
| `/admin/branches`, `/admin/branches/:branchId` | branch view-any | branches and detail modal: create/edit/activate/deactivate, working hours/holidays/timezone. These determine SLA business time. | branches/calendar |
| `/admin/departments`, `/:departmentId` | department view-any | manage support routing containers; assign staff through Users placement controls. | departments |
| `/admin/teams`, `/:teamId` | team view-any | manage team grouping used by ticket scope. | teams |
| `/admin/users` | user manage | list/filter users; invite, activate/deactivate, roles/branch/department placement. Invitation hands work/access to a new person. | users/invite/placement |
| `/admin/roles`, `/:roleId` | roles manage | list/create/edit custom roles, permission keys and members. Avoid changing system role intent without testing scopes. | roles/catalogue |
| `/admin/ticket-catalogue` | statuses or categories manage | category tree/custom fields and ticket status catalogue. Category selection changes required fields; status lifecycle type controls transitions. | categories/statuses |
| `/admin/sla-policies` | SLA policy view | list/create/edit policy and targets; management permission required for writes. | SLA policies |
| `/admin/automation-rules` | automation rule view | rules and execution history; create/edit/delete requires manage. Rules act later through hooks/sweeps. | automation rules/executions |
| `/admin/channels` | any channel capability | email replay, public forms, message templates and staff chat sessions; forms publish public entry routes and chat accepts/transfer/ends visitor sessions. | channels endpoints |
| `/admin/settings` | 2FA policy manage | organisation authentication/2FA policy affecting next staff login. | auth policy |

## Portal and public surfaces

| Route / component | Access | Business intent, action and expected result | API |
|---|---|---|---|
| `/portal/login`, `/portal/register`, `/portal/verify` | portal public | create/verify/login customer account; verification must precede normal protected work. | portal auth |
| `/portal/tickets` | authenticated portal | own-customer ticket list; create link. Never staff queue. | portal tickets |
| `/portal/tickets/new` | authenticated portal | submit customer request; staff receives ticket in permitted department/queue. | POST portal tickets |
| `/portal/tickets/:id` | authenticated portal and owned record | read public conversation, send public message, submit ticket feedback where eligible, download allowed file. | portal messages/feedback/attachments |
| `/portal/account` | authenticated portal | current portal customer identity/session account view. | portal me |
| `/portal/help`, `/portal/help/:slug` | public | public knowledge discovery/article; only eligible published article is visible. | public knowledge |
| `/portal/track/:token` | tracking-token holder | guest ticket tracking; token, not staff/portal login, grants narrow access. | guest ticket endpoint |
| `/forms/:formKey`, `/forms/submissions/:trackingToken` | public | configured form submission and token-tracked status. A valid active form must exist. | public form endpoints |
| `/chat` | public | create visitor session and converse; a permitted staff member must accept/transfer/end through Channels admin. | public chat endpoints |
| `*` `NotFoundState` | anyone | unknown SPA route; no business action. | n/a |

## Easy-to-miss features

- Ticket list has saved views and bulk status/assignment operations; URL filters persist. `SavedViewsBar.tsx`, `TicketBulkActions.tsx`.
- Workbench has watchers, mentions (internal-only), delivery retry/events, AI suggestions, history, SLA clock and ticket-bound agent tasks.
- Backend has endpoints for claim, transfer, reopen, spam, links, merge/split and ticket tags, but the present workbench/list does not expose each as a complete UI control. Treat them as API capabilities, not a pass condition for browser-only testing.
- Customer detail has merge/duplicate review, blocked state, files, notes, timeline and ERP context; contact CRUD exists backend-side but is not an evident current screen panel.
- Channels admin has tab-specific permissions; public routes must be tested in a separate clean browser context.
- Token issuance is intentionally one-time reveal; record it only in a secure local test note, then revoke it.
