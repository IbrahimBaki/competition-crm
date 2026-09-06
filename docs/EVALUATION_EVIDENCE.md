# Evaluation Evidence: Support CRM

## Project statement

Support CRM implements a complete support-operations architecture: a Laravel 12 API, React 18 staff SPA, customer portal, and public intake surfaces. The repository provides implementation evidence across versioned routes, domain services, database migrations, policies, jobs, configuration, API contracts, translations, tests, deployment configuration, and planning documentation.

## Architecture and technical design

| Layer | Implementation evidence |
|---|---|
| API and application | `routes/api.php`; `bootstrap/app.php`; `app/Domains/*/Http/Controllers`; Laravel 12 in `composer.json` |
| Domain design | `app/Domains/{Ai,Automation,Channels,CustomerPortal,Customers,Integrations,Knowledge,Notifications,Organisation,Portal,Reporting,Security,Sla,Ticketing,Workspace}` |
| Data | 104 files in `database/migrations/`; UUID model trait `app/Support/Models/GeneratesUuid.php` |
| SPA | `frontend/src/router.tsx`, `frontend/src/app/`, `frontend/src/features/`, React Router/TanStack Query/i18next in `frontend/package.json` |
| API contract | `docs/api/openapi.yaml`, frozen baseline `docs/api/openapi.v1.frozen.yaml`, contract policy `docs/contracts/api-contract-freeze.md` |
| Design system | `design-system/MASTER.md`; reusable components in `frontend/src/design-system/` |
| Operations | `routes/console.php`; command classes in `app/Console/Commands/`; jobs in domain modules |

The API is versioned under `/api/v1`. It separates public, portal, and staff routes: public endpoints use throttling/bot protection; portal routes use `auth:portal` and `portal.auth`; staff routes use `auth:sanctum` and `portal.deny`. This boundary is explicit in `routes/api.php`.

## Requirement coverage

The source specification is structured in [UI overview](ui/00-overview.md) and its 13 module specifications. The detailed requirement-by-requirement implementation, architecture, repository evidence, verification, and operational behaviour are in [REQUIREMENTS_TRACEABILITY.md](REQUIREMENTS_TRACEABILITY.md). Coverage includes identity, organisation, customers, tickets, SLA, automation, channels, knowledge base, workspace/notifications, AI, reports, integrations, customer portal, public forms, public chat, Arabic/English/RTL, responsive design, and accessibility.

## Workflow evidence

### Ticketing, SLA, and automation

Ticket routes cover create/list/show/update, history, transitions/reopen/spam, merge/split, assignment/claim/transfer, links, messages/retry/delivery events, watchers, categories, and saved views. Key components include `app/Domains/Ticketing/Actions/`, `Services/RecordTicketEvent.php`, policies, request validators, and `frontend/src/features/tickets/`.

SLA policy, ticket-SLA, and reset routes are backed by `app/Domains/Sla/`, including `SlaClockService.php`, working-time support, clocks, pause intervals, and breach models/migrations. `routes/console.php` schedules `sla:sweep` every five minutes. Automation rules, executions, escalation, condition evaluation, and a scheduled `automation:sweep` are provided by `app/Domains/Automation/`.

### Customer experience and knowledge

Customer 360 workflows include contacts, notes, protected attachments, timelines, identity resolution, duplicate candidates, and merge actions. Evidence includes `app/Domains/Customers/`, `frontend/src/features/customers/`, customer migrations, and customer feature/unit tests.

Knowledge supports category trees, article lifecycle/visibility, rendering, search/indexing, versions/restores, audience resolution, and feedback. The public knowledge routes provide help-centre browse/search/article/feedback access without staff authentication; `app/Domains/Knowledge/Services/Visibility/` scopes audiences.

### Channels, portal, reports, and integrations

Channel ingress is implemented for inbound email, configurable WhatsApp/SMS provider webhooks and delivery receipts, public web forms, and live chat. Provider delivery/inbound processing uses jobs; public routes have throttling and public protection. The customer portal separates authentication, account verification, ownership-scoped ticket access, messages, feedback, and guarded attachments. Guest tracking, public forms/submission tracking, and public chat are separately routed.

Reporting exposes report querying, exports, and schedules; `GenerateReportExportJob.php` and the scheduled report sweep support asynchronous delivery. Integrations provide API token lifecycle, webhook subscription/delivery/retry infrastructure, ERP context, and bulk imports. These are configurable via `config/integrations.php` and deployment environment variables; provider delivery is deployment-credential dependent by design.

## Security controls

Security is implemented as application behaviour rather than represented only by scanning.

| Control | Code-level evidence | Behaviour |
|---|---|---|
| Authentication and sessions | `AuthController.php`, `AuthenticateStaff.php`, `config/auth.php`, `config/session.php`, `config/sanctum.php` | Staff uses stateful Sanctum; login/logout regenerate session/token state. |
| Two-factor authentication | `TwoFactorController.php`, `EnableTwoFactor.php`, `CompleteTwoFactorChallenge.php`, `EnforceTwoFactorPolicy.php` | Authentication is challenged before the protected session flow completes; recovery codes and policy enforcement are supported. |
| RBAC and scope | `PermissionKey.php`, `Scope.php`, `ResolveEffectiveScope.php`, domain `Policies/` | Server-side permissions and organisation scope determine access; frontend navigation/action guards mirror permission keys. |
| Portal isolation | `routes/api.php`, `app/Domains/Portal/Services/Visibility/`, `PortalGuardIsolationTest.php` | Portal tokens and staff tokens are segregated; portal ticket/attachment visibility is owner-scoped. |
| Input validation | domain `Http/Requests/`, `AttachmentRules.php`, `StaffPasswordRules.php` | Requests use server-side rules, including file MIME/size allowlists and password policy. |
| CSRF, rate controls, public hardening | `bootstrap/app.php`, `config/cors.php`, `ProtectPublicEndpoint.php`, route `throttle:*` middleware | Stateful browser requests use Laravel CSRF/session controls; public, portal, form, chat, and upload endpoints are rate-limited/protected. |
| Audit trail | `AuditLogger.php`, `AuditLog.php`, `AuditLogPolicy.php`, audit migrations | Sensitive security and business actions record audit events; immutable audit constraints are represented in migration and tests. |
| Idempotency/concurrency | `EnforceIdempotency.php`, `idempotency_keys` migration, ticket version guards | Retried protected requests can be deduplicated; ticket version conflicts expose current state. |
| Webhooks/API tokens | `WebhookSigner.php`, `DeliverWebhookJob.php`, `AuthenticateApiToken.php`, `IssueApiToken.php` | Outbound webhooks are timestamp-signed; issued plaintext API tokens are not persisted/logged. |
| Secrets, logging, retention | `.env.example`, `docker/env-additions.txt`, `config/*`, `LogRedactionTest.php`, retention handlers | Credentials are environment-configured; retention, backup, and log-redaction mechanisms are implemented. |

## Testing and verification inventory

- Backend: 130 `*Test.php` files (91 feature and 39 unit); the runner lists 635 tests. Feature areas span AI, API, automation, channels, customers, integrations, knowledge, observability, organisation, portal, reporting, security, SLA, ticketing, and workspace.
- Frontend: 39 Vitest files with 159 declared `it`/`test` cases; current suite discovery produced 164 executed test cases. Tests cover API envelopes/mutators, permission/session rules, localisation/design-system accessibility, ticket/customer/admin wiring and interactions, and navigation.
- End-to-end: seven Playwright specifications in `frontend/e2e/`: `core-workflows`, `route-smoke`, `responsive`, `tickets-v2`, `ticket-workbench-v2`, `customers-v2`, and `workspace-v2`.
- Contract and quality: OpenAPI source/frozen contract, `make api-lint`, strict TypeScript, ESLint with zero-warning threshold, Vite production build, Pint, PHPStan, and Pest commands are provided by `Makefile` and package scripts.

The exact commands and results from this documentation pass, including successful build and the factual status of test/static-analysis runs, are recorded in [VERIFICATION_REPORT.md](VERIFICATION_REPORT.md).

## CI/CD, delivery, and maintainability

The delivery path is codified in `Makefile`, `composer.json`, `frontend/package.json`, `docker/vhost-competition-crm.conf`, and `docker/env-additions.txt`. The SPA build is `frontend/dist`; Apache history fallback supports client routing. Environment configuration defines CORS, shared cookie-domain/Sanctum settings, queues, attachments, backup/retention, channel/provider, reporting, AI, ERP, and webhook controls.

Maintainability is supported by domain boundaries, typed API contract generation, immutable contract policy, reusable frontend design components, shared error/envelope handling, translations, explicit migrations, queued jobs, schedulable commands, and focused unit/feature/E2E test organisation. Planning and delivery history are present in `FRONTEND_REBUILD_EXECUTION.md`, `docs/ui/`, contract documents, and Git commits such as `a8ab5ee`, `99df91b`, and `2a725eb`.
