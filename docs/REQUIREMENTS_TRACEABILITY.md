# Requirements Traceability

## Scope and method

This matrix traces the supplied UI specification (docs/ui/00-overview.md and modules 01–13) to implementation evidence. Evidence recovery searched routes, React routes/pages/components, domain controllers/actions/services, models/migrations, jobs/events/listeners, OpenAPI, policies/permissions, tests, configuration, translations, webhooks/providers, Git history, and existing engineering documentation.

| Requirement | Coverage | Main evidence | Verification |
|---|---|---|---|
| Authentication, identity, RBAC | Implemented | routes/api.php; app/Domains/Security; frontend/src/pages/auth | Security and frontend permission/session tests |
| Organisation and customers | Implemented | Organisation and Customers domains; React feature modules | Feature/unit/component tests |
| Ticketing and interaction history | Implemented | Ticketing domain, staff routes, ticket UI | Feature/unit tests and E2E inventory |
| SLA, assignment, automation | Automated | Sla, Automation, Workspace, routes/console.php | SLA/automation tests |
| Channels | Integrated | Channels domain, public/provider routes, jobs | Channel tests |
| Knowledge base | Implemented | Knowledge domain, public/staff routes | Knowledge tests |
| AI assistance | Configurable | Ai domain, feature gate/configuration | AI feature tests |
| Reports and exports | Automated | Reporting domain, export job, schedules | Reporting tests |
| Customer portal | Implemented | Portal domain and portal React routes | Portal/isolation tests |
| Public forms and live chat | Integrated | WebForm/Chat domains and public pages | Channel tests/E2E inventory |
| Integrations | Configurable | Integrations domain, tokens/webhooks/import/ERP | Integration tests |
| Arabic, English, RTL, responsive/a11y | Implemented | i18n, LocaleProvider, design system, E2E specs | axe/design-system and responsive E2E inventory |

## Identity, security, and access control

### Authentication, roles, permissions, 2FA, and audit logs

**Implementation**

Staff sign-in/out, password reset, invitation acceptance, user lifecycle, roles, permission catalogue, configurable two-factor authentication, policy management, audit-log viewing, and personal-data erasure are provided through the security domain. Access is permission-key and scope based; it does not rely on role-name comparisons. The frontend protects routes and action visibility with permission guards.

**Architecture**

Laravel Sanctum protects staff routes; portal routes use a separate guard. Security actions and policies own business rules, while request objects validate input. AuditLogger records sensitive actions; scope resolution is reusable by domains. React AuthProvider, ProtectedRoute, RequirePermission, and ActionGuard consume the server-provided permission set.

**Repository evidence**

routes/api.php; app/Domains/Security/Actions/AuthenticateStaff.php; CompleteTwoFactorChallenge.php; InviteUser.php; AnonymisePersonalData.php; app/Domains/Security/Policies; PermissionKey.php; ResolveEffectiveScope.php; AuditLogger.php; frontend/src/auth; tests/Feature/Security; tests/Unit/Security.

**Verification**

Security test inventory covers auth, CSRF, scope, administrator invariants, audit trails, invitations, 2FA, and password rules. See docs/VERIFICATION_REPORT.md for current-run status.

**Operational behavior**

Users authenticate as staff, complete 2FA when required, and receive only routes/actions corresponding to their permission keys. Security-sensitive activity is audit-recorded; portal credentials cannot be used for staff routes.

## Organisation and customer management

### Organisation, customers, customer 360, and customer history

**Implementation**

Branches, departments, teams, working hours, holidays, and user placements model the support organisation. Customer operations include profiles, company accounts, contacts, notes, attachments, timeline events, block/unblock, identity normalisation, duplicate candidates, and merge previews/actions.

**Architecture**

Organisation supplies structure and working-time inputs; Customers owns the customer aggregate and activity records. Controllers use policies and form requests; React list/detail features use typed API mappers and query hooks. Customer/event/duplicate migrations persist the workflow.

**Repository evidence**

app/Domains/Organisation; app/Domains/Customers; database/migrations/2026_08_26_080100_create_company_accounts_table.php; 2026_08_26_080200_create_customers_table.php; 2026_08_26_080300_create_customer_contacts_table.php; 2026_08_26_080500_create_customer_events_table.php; frontend/src/features/customers; frontend/src/features/admin/organisation; tests/Feature/Customers; tests/Feature/Organisation.

**Verification**

Customer and organisation feature/unit suites exist, alongside frontend query, detail, merge-preview, and organisation wire/component tests.

**Operational behavior**

Authorised staff can maintain the organisation structure and use a customer profile as the context for contacts, support history, protected files, notes, duplicate review, and merge decisions.

## Support operations

### Ticketing, interaction history, assignment, and collaboration

**Implementation**

Ticket queues and saved views support triage. Ticket workflows include status transitions, reopen/spam actions, assignment/claim/agent-or-department transfer, public/internal messages, message delivery/retry events, watchers, links, ticket merge/split, category fields, history, and version-aware conflict handling.

**Architecture**

Ticketing separates controllers, actions, policies, lifecycle maps, request validation, models, events, and SLA hooks. The React ticket list/detail/composer/status/SLA components use route and permission guards.

**Repository evidence**

app/Domains/Ticketing; routes/api.php; frontend/src/features/tickets; frontend/src/pages/TicketDetailPage.tsx; tests/Feature/Ticketing; tests/Unit/Ticketing; frontend/e2e/tickets-v2.spec.ts; ticket-workbench-v2.spec.ts; core-workflows.spec.ts.

**Verification**

Ticket transition, category, reference, version-guard, reopening, delivery-state, assignment, message visibility, link, and concurrency test files are present; frontend unit and E2E specifications target the workbench.

**Operational behavior**

Agents work from queues into a ticket workbench. The server determines available transitions and scoped visibility, records history, protects internal notes, and returns a conflict state if an update is based on an outdated version.

### SLA, automatic assignment, automation, escalation, and workspace

**Implementation**

SLA policies define targets, clocks, pauses, and breaches. Automation rules evaluate conditions and record executions; escalation is exposed as an automation action. The workspace supplies agent tasks, state changes/events, quick replies/rendering, queues, notifications, preferences, and delivery attempts.

**Architecture**

Sla, Automation, Workspace, and Notifications domains integrate through ticket events and scheduled commands. Working-time calculations use dedicated services rather than direct time arithmetic. Scheduled sweeps run SLA checks every five minutes, automation every ten minutes, and task reminders every five minutes.

**Repository evidence**

app/Domains/Sla/Services/SlaClockService.php; app/Domains/Automation/Services; TicketEscalationController.php; app/Domains/Workspace; app/Domains/Notifications; routes/console.php; tests/Feature/Sla; Automation; Workspace; tests/Unit/Sla; Automation.

**Verification**

Focused SLA tests include SlaClockServiceTest.php and no-direct-time-arithmetic enforcement. Automation tests include rule ordering, condition evaluation, and smoke coverage.

**Operational behavior**

The system advances SLA/automation work on a schedule, records workflow state, and makes staff tasks, queue context, quick replies, and notifications available in the workspace.

## Channels, knowledge, AI, reporting, and integrations

### Omnichannel communications

**Implementation**

Inbound email, public web forms, live chat, and WhatsApp/SMS provider endpoints are implemented. Provider message delivery receipts and inbound messages are processed asynchronously; templates, sessions, form definitions/submissions, transcript controls, and public endpoints are part of the channels domain.

**Architecture**

Public endpoints have public.protect and route-specific throttles. Email and messaging jobs process transport work. Configuration provides pluggable transports and webhook secrets without embedding credentials in source.

**Repository evidence**

app/Domains/Channels; routes/api.php; config/channels.php; docker/env-additions.txt; frontend/src/pages/public/PublicChannelPages.tsx; tests/Feature/Channels; tests/Unit/Channels.

**Verification**

Email correlator/classifier/quoted-text and web-form payload validator unit tests, plus channel feature coverage, are present. Provider-side delivery requires deployment credentials.

**Operational behavior**

Visitors can submit protected forms and start protected chat sessions; staff can operate channel workflows. External provider transports are activated by environment configuration.

### Knowledge base and AI suggested solutions

**Implementation**

Knowledge articles support categories, bilingual content, lifecycle state, visibility/audience control, search indexing, rendering, versions/restoration, and feedback. AI can classify tickets, generate summaries and suggested replies, recommend knowledge articles, power a chatbot boundary, track usage, apply budgets, sanitise payloads, and require approval before AI-produced customer content is sent.

**Architecture**

Knowledge uses visibility/query and search services; Ai uses provider interfaces, feature gates, privacy sanitation, budget guards, persisted suggestion/usage state, and ticket assist controllers. The frontend offers staff knowledge and AI operation routes, while public knowledge has a purpose-specific route group.

**Repository evidence**

app/Domains/Knowledge; app/Domains/Ai; config/ai.php; frontend/src/pages/operations/OperationsPages.tsx; frontend/src/pages/operations/CreationPages.tsx; tests/Feature/Knowledge; tests/Feature/Ai; tests/Unit/Knowledge.

**Verification**

Knowledge visibility/versioning/feedback/category tests and AI feature tests are included. AI provider execution is environment-dependent and configured through feature flags/provider credentials.

**Operational behavior**

Staff can curate and publish scoped knowledge; customers access public help content. AI assistance is gated, usage-controlled, privacy-sanitised, and approval-aware.

### Reports, exports, integrations, and operational readiness

**Implementation**

Reports support filters, dashboard views, exports, schedules, and scheduled delivery. Integrations expose API tokens, signed webhook subscription/delivery/retry infrastructure, ERP customer context, and import runs/rows. Backup, verification, retention, health endpoints, request IDs, and scheduled maintenance support operations.

**Architecture**

Report exports use GenerateReportExportJob; webhook delivery uses DeliverWebhookJob and WebhookSigner; imports use RunImportJob. The console scheduler triggers backup, backup verification, retention, automatic close, reporting, email, chat, SLA, task, and automation work without overlapping jobs.

**Repository evidence**

app/Domains/Reporting; app/Domains/Integrations; app/Console/Commands/BackupRunCommand.php; BackupVerifyCommand.php; RetentionPurgeCommand.php; ReportScheduleSweepCommand.php; routes/console.php; config/reporting.php; integrations.php; backup.php; retention.php; tests/Feature/Reporting; Integrations; Observability; docs/operations/backup-restore.md.

**Verification**

Report/integration/health/request-ID test directories are included. Performance-budget documentation provides historical measured MySQL evidence and query-count targets at docs/api/performance-budget.md.

**Operational behavior**

Authorised users can query/export/schedule reports and configure integration boundaries. Jobs deliver exports, webhooks, and imports asynchronously; credentials and provider transport settings are deployment configuration.

## Customer portal and public experience

### Portal, public knowledge, forms, chat, Arabic/English RTL, and responsive access

**Implementation**

The portal supports registration, verification, login/logout, account details, customer-owned ticket list/create/detail/message/feedback, guarded attachment download, and guest tracking. Public help, web form/submission tracking, and chat are routed independently. The SPA includes English and Arabic catalogs, locale persistence, RTL document direction, reusable accessible components, and responsive E2E coverage.

**Architecture**

Portal account authentication and visibility services enforce customer ownership. Public endpoints are not the same routes as staff operations. i18next and LocaleProvider set document lang/dir; React Router has separate portal/public route trees; the shared design system uses accessible component primitives.

**Repository evidence**

app/Domains/Portal; app/Domains/CustomerPortal; frontend/src/pages/portal; frontend/src/portal; frontend/src/i18n/ar.json; en.json; LocaleProvider.tsx; frontend/src/design-system; frontend/e2e/core-workflows.spec.ts; responsive.spec.ts; route-smoke.spec.ts; tests/Feature/Portal; frontend/src/__tests__/portal-isolation.test.ts.

**Verification**

Portal feature tests cover authentication, visibility, feedback, and token isolation. Design-system tests include axe checks, keyboard dialog focus restoration, accessible menus/tabs, and RTL icon behaviour. Playwright E2E specifications provide end-to-end and responsive workflow coverage.

**Operational behavior**

Customers use a portal view restricted to their own support data; visitors can use deliberately public, rate-protected support surfaces. Users can work in Arabic RTL or English LTR, across responsive layouts.
