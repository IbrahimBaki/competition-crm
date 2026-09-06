# Support CRM

Support CRM is a bilingual customer-support platform with a Laravel 12 API and a React 18 single-page application. It provides staff operations, customer and ticket management, SLA and automation workflows, knowledge management, reporting, configurable integrations, and a customer portal/public intake experience.

This repository is organised as an evaluator-facing engineering package. Start with [evaluation evidence](docs/EVALUATION_EVIDENCE.md), [requirements traceability](docs/REQUIREMENTS_TRACEABILITY.md), and the [verification report](docs/VERIFICATION_REPORT.md).

## Implemented product surfaces

- Staff authentication, invitation, password recovery, configurable 2FA, RBAC, permission scopes, audit logs, and data-protection operations.
- Organisation hierarchy (branches, departments, teams, staff placement), customer 360 profiles, contacts, notes, attachments, timeline, duplicate resolution and merge controls.
- Ticket queues, saved views, lifecycle/status transitions, assignment and transfer, messages and internal notes, watchers, links, merge/split, delivery tracking, optimistic-version conflict handling, and SLA clocks.
- SLA policy administration, breach handling, automation rules and executions, escalations, agent tasks, quick replies, and notifications.
- Email, web-form, WhatsApp/SMS provider, and live-chat channels; knowledge base publishing, visibility, search, versioning, feedback, and AI-assisted ticket workflows.
- Management reports, export and scheduled report delivery; webhook subscriptions/deliveries, API tokens, ERP context, and bulk import runs.
- Customer portal registration/verification/login, customer-scoped tickets and attachments, guest tracking, public knowledge, public forms, and public chat.

## Architecture

The backend is a domain-oriented Laravel application under `app/Domains/` with 14 areas: AI, automation, channels, customer portal, customers, integrations, knowledge, notifications, organisation, portal, reporting, security, SLA, ticketing, and workspace. Controllers and form requests expose the versioned API in [routes/api.php](routes/api.php); policies, permission keys, scope filters, jobs, events, migrations, and tests keep business rules close to each domain.

The React SPA in `frontend/` uses React Router, TanStack Query, i18next, TypeScript strict mode, generated API types, and an application design system. [frontend/src/router.tsx](frontend/src/router.tsx) declares staff, portal, and public routes. The UI follows [design-system/MASTER.md](design-system/MASTER.md), including responsive behaviour, keyboard focus, contrast, motion, and RTL expectations.

The API contract is [docs/api/openapi.yaml](docs/api/openapi.yaml). It contains 162 documented path entries and is paired with an additive v1 freeze at [docs/contracts/api-contract-freeze.md](docs/contracts/api-contract-freeze.md). The frontend API client is generated through `npm run api:generate` in `frontend/`.

## Data, workflow, and operations

The schema is captured in 104 Laravel migrations: tickets, messages, statuses, categories, SLA clocks and breaches; customer identity and activity; organisation placement; knowledge versioning; notification delivery; portal accounts; channel ingress; integrations; and audit/idempotency records. UUID model identifiers are used throughout the domain model.

Scheduled operational work is declared in [routes/console.php](routes/console.php): backup and verification, retention purge, automatic close, SLA sweep, task reminders, inbound-email re-sweep, chat session sweep, automation sweep, and report schedule sweep. Queue-backed jobs handle provider messaging, inbound email, webhook delivery, imports, and report exports.

## Security engineering

Staff endpoints are protected by Sanctum and portal denial middleware; portal endpoints use a separate portal guard. Permission keys and scoped policies enforce RBAC without hard-coding role names. The application also includes request validation, CSRF/session rotation, 2FA enrolment/challenge/recovery, password rules, rate limiting and bot/public-endpoint protection, immutable audit records, idempotency keys, attachment type/size restrictions, signed outbound webhooks, API-token controls, configuration-based secret handling, and retention/backup processes. Full evidence is in [docs/EVALUATION_EVIDENCE.md](docs/EVALUATION_EVIDENCE.md#security-controls).

## Arabic, English, RTL, accessibility, and responsive design

The frontend loads Arabic and English translations from `frontend/src/i18n/`, persists the selected locale, and sets the document `lang` and `dir` (`rtl` for Arabic). Shared design-system components use accessible names, visible focus, Radix keyboard/dialog primitives, error association, and semantic navigation; relevant tests include `frontend/src/design-system/__tests__/components.test.tsx`. Responsive Playwright specifications cover core routes at multiple viewports in `frontend/e2e/`.

## Local development and delivery

Prerequisites: Docker/Docker Compose, Node.js 18+, and Make. The canonical local setup uses the API at `competition-crm.azmsquad.localhost` and the SPA at `app.competition-crm.azmsquad.localhost`.

```bash
make install
make migrate
make seed

cd frontend
npm ci
npm run api:generate
npm run dev
```

The split-host cookie, Sanctum, and CORS configuration is documented in [docker/env-additions.txt](docker/env-additions.txt); [docker/vhost-competition-crm.conf](docker/vhost-competition-crm.conf) serves Laravel from `public/` and the SPA from `frontend/dist/` with history fallback.

Production delivery builds the SPA, migrates the API, and caches Laravel configuration:

```bash
cd frontend && npm run build
php artisan migrate --force
php artisan config:cache
```

Run quality checks with `make lint`, `make test`, `make api-lint`, `make fe-test`, `make fe-build`, and `cd frontend && npm run test:e2e`. See the verification report for the exact documentation-pass results and test inventory.

## Planning, specification, and maintainability

The UI specification and task breakdown are in [docs/ui/00-overview.md](docs/ui/00-overview.md) and module documents `01` through `13`; execution planning is in [FRONTEND_REBUILD_EXECUTION.md](FRONTEND_REBUILD_EXECUTION.md). Shared conventions cover API envelopes, bilingual content, automation rules, AI assistance, localisation, audit trails, uploads, and working-time SLA calculations. Git history records staged frontend delivery milestones.

AI usage is bounded by feature gates, provider configuration, payload sanitisation, budget controls, usage records, review/approval states, and explicit approval before AI-generated customer content is sent. Human verification remains part of delivery through the documented test, build, API-contract, and E2E commands.
