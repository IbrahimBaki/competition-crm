# Verification Report

## Documentation-pass summary

This report distinguishes test inventory from commands executed during the documentation pass on 2026-09-06. It records observed results without treating environment-dependent execution as a substitute for a successful check.

| Area | Command | Result | Evidence |
|---|---|---|---|
| Frontend production delivery | cd frontend && npm run build | **PASS** — TypeScript project build and Vite production bundle completed in 5.05s; 2,278 modules transformed | frontend/dist generated; largest vendor asset 512.66 kB minified / 160.88 kB gzip; Vite emitted a size advisory only |
| Frontend type checking | cd frontend && npm run typecheck | **PASS** | TypeScript strict-mode command completed before lint/build in the documented combined run |
| Frontend lint | cd frontend && npm run lint | **PASS** | ESLint command with --max-warnings=0 completed before build |
| Frontend unit/integration tests | cd frontend && npm run test | Executed: 39 files, **162 passed / 2 failed** (164 total) | Failing assertions are in frontend/src/features/admin/api/__tests__/wire.test.ts for branch/department name mapping; React/Router advisory output was non-fatal |
| Backend security-suite alias | vendor/bin/pest --testsuite=Security in azm-php82 | Command completed, no tests selected | The repository does not define a Pest testsuite named Security; targeted paths are the applicable invocation |
| Targeted backend security paths | vendor/bin/pest tests/Feature/Security tests/Unit/Security in azm-php82 | Execution started; current container reported feature failures and did not complete within the captured 30-second window | The same pass observed PHPUnit doc-comment metadata deprecation notices. Source-level security coverage remains inventoried below. |
| Backend formatting/static analysis | vendor/bin/pint --test && vendor/bin/phpstan analyse --no-progress in azm-php82 | Pint completed with 6 style issues; PHPStan did not run because the shell used && | Style issues are named in the command output: three controllers, E2eTestSeeder.php, and PortalAuthTest.php |

## Test inventory and purpose

| Layer | Inventory | Purpose and representative paths |
|---|---:|---|
| Laravel | 130 *Test.php files; 91 feature, 39 unit; Pest lists 635 tests | API behaviour and regressions across security, portal, ticketing, SLA, automation, channels, customers, integrations, knowledge, reporting, observability, organisation, workspace, AI, and support conventions. |
| React/Vitest | 39 test files; 159 declared it/test cases; current run enumerated 164 executed cases | API envelope/mutator handling, permission and session management, a11y components, navigation, ticket/customer/admin behaviours, and error states. |
| E2E/Playwright | 7 specifications | End-to-end core workflows, route smoke coverage, tickets, workbench, customers, workspace, and responsive behaviour in frontend/e2e. |

Representative backend paths include tests/Feature/Security, tests/Feature/Portal, tests/Feature/Ticketing, tests/Unit/Sla, and tests/Unit/Automation. Frontend evidence includes frontend/src/design-system/__tests__/components.test.tsx, frontend/src/auth/__tests__, and feature-level tests under frontend/src/features.

## Commands for repeatable verification

    # Backend: run in the project PHP runtime
    make test
    make lint
    make api-lint

    # Frontend
    cd frontend
    npm run test
    npm run typecheck
    npm run lint
    npm run build
    npm run test:e2e

make test runs Pest in the configured azm-php82 container. make lint runs Pint and PHPStan. make fe-test combines frontend Vitest, typecheck, and lint; make fe-build produces the deployable SPA. Playwright expects the canonical SPA/API host and runtime setup declared in frontend/playwright.config.ts, Docker vhost configuration, and docker/env-additions.txt.

## Verification evidence by engineering criterion

### Requirements and workflows

Routes, controllers, form requests, policies, migrations, UI routes, and focused test folders provide implementation evidence for ticketing, SLA, automation, portal, knowledge, channels, AI, reports, integrations, organisation, and customers. Detailed traceability is in docs/REQUIREMENTS_TRACEABILITY.md.

### Security

The test inventory includes authentication, CSRF, administrator invariants, RBAC/scope enforcement, audit immutability/listing, invitations, 2FA, password policy, log redaction, attachment rules, portal/staff isolation, and API-token lifecycle. Code-level controls are documented in docs/EVALUATION_EVIDENCE.md under Security controls, including Sanctum/portal guards, policies, rate limits, validation, audit logger, idempotency, webhook signing, upload rules, retention, and configuration-based secret management.

### Frontend quality and accessibility

Strict TypeScript, zero-warning ESLint, and a production build passed in this run. frontend/src/design-system/__tests__/components.test.tsx includes axe, keyboard focus, dialog, menu/tab, accessible-name, and RTL coverage. frontend/e2e/responsive.spec.ts provides responsive test coverage. The frontend test suite was executed and its exact result is reported above.

### API and integration verification

docs/api/openapi.yaml is the versioned API contract; the frozen v1 contract and additive-only policy protect compatibility. The repository provides OpenAPI lint/client-generation commands, typed frontend generation, webhook transport/signing, API token lifecycle, ERP context, and import/report jobs. Runtime verification of external providers requires their deployment credentials and endpoints.

### Delivery, CI/CD, and operational readiness

Makefile, Composer scripts, frontend package scripts, Docker vhost configuration, environment templates, migrations, health routes, queued jobs, console schedules, backup/restore documentation, and retention configuration define the delivery workflow. The documentation-pass production build is a successful delivery artifact verification. Existing historical evidence in docs/api/performance-budget.md records MySQL performance-budget measurements and query-count objectives; it is distinguished from the commands executed in this pass.

## Notes on repeatability

The current Docker environment supplies azm-php82 and MySQL. End-to-end browser checks need the configured canonical hosts and active application runtime. Provider-specific email, WhatsApp/SMS, ERP, webhook, and AI execution require environment credentials; their application integration boundaries, validation, job handling, and configuration are present in the repository.
