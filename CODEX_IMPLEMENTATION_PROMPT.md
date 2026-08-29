# CODEX IMPLEMENTATION PROMPT

The audit phase is complete.

Proceed now with the COMPLETE frontend rebuild and rebranding.

You are responsible for executing the project end-to-end within the frontend/integration scope.

Before modifying code, read:

- `AGENTS.md`
- `FRONTEND_REBUILD_EXECUTION.md`
- `design-system/MASTER.md`
- the audit output you just produced
- `docs/frontend-rebuild-status.md` if it already exists

If `docs/frontend-rebuild-status.md` does not exist, create it from your audit before implementation.

Use UI UX Pro Max for UI/UX guidance throughout the implementation.

## Core Goal

Deliver a completely rebranded, production-ready React frontend integrated with the existing Laravel backend.

This is NOT only a visual redesign.

The final result must include:

- every actual application page
- every actual user role
- real backend integration
- working authentication
- correct authorization
- correct queries and mutations
- working forms
- working tables/lists
- responsive UI
- accessible UI
- consistent design system
- reproducible local runtime
- passing production build

## Execution Rules

Execute `FRONTEND_REBUILD_EXECUTION.md` phase by phase.

Do not stop after:
- shared components
- dashboard redesign
- several representative screens
- application shell
- successful compilation
- successful production build

Continue until the complete coverage matrix is finished.

Keep `docs/frontend-rebuild-status.md` updated while working.

For each discovered route/page, track:

- role
- route
- backend/API dependencies
- permission
- UI status
- integration status
- responsive status
- verification status

Do not summarize multiple real pages as "same pattern".
Implement and verify each one.

## Design

Treat `design-system/MASTER.md` as the global source of truth.

Completely rebrand the frontend.

Preserve business functionality, not poor presentation.

Create one coherent modern B2B CRM / operations application.

Standardize:
- typography
- colors
- spacing
- radii
- borders
- elevation
- iconography
- application shell
- navigation
- page headers
- breadcrumbs
- forms
- buttons
- tables
- cards
- badges
- tabs
- dialogs
- dropdowns
- alerts/toasts
- loading/skeleton states
- error states
- empty states
- pagination
- filtering
- search
- confirmation dialogs

Do not introduce random page-specific visual systems.

## Backend Integration

Treat Laravel implementation as the source of truth for:
- business capabilities
- roles
- permissions
- validation
- APIs

Treat the repository's generated OpenAPI / TanStack Query integration as the preferred frontend API pattern.

Do not replace real backend integration with mocks.

Do not introduce ad-hoc raw `fetch()` calls when the intended generated API abstraction already supports the workflow.

Verify all important queries and mutations.

After mutations, ensure relevant cached queries are invalidated or updated correctly.

## Authentication and Authorization

Fully verify and fix:
- staff login
- portal login
- logout
- current user loading
- session expiration
- 401
- 403
- route guards
- redirects
- role-aware navigation
- permission-aware actions

Derive roles and permissions from backend code.
Do not invent them.

## Runtime Configuration

Resolve the known hostname inconsistency by inspecting the actual repository.

Known names include:
- `competition-crm.azmsquad.localhost`
- `app.support-crm.localhost`

Inspect and reconcile:
- Vite `allowedHosts`
- API base URL
- Apache/reverse proxy
- Docker
- frontend env
- backend env
- Laravel APP_URL
- CORS
- cookies
- session domain
- Sanctum/auth configuration if used

Create one reliable local runtime flow.

Do not blindly trust documentation if it conflicts with checked-in implementation.

## Page Completion Rule

A page is NOT complete merely because it renders.

A page is complete only when applicable requirements pass:

- correct route
- correct role access
- real backend data
- real mutations
- loading state
- empty state
- error state
- client validation
- server validation display
- success feedback
- disabled/submitting states
- permission-aware controls
- responsive behavior
- keyboard/focus usability
- correct post-mutation refresh
- no dead buttons
- no fake data
- no placeholder UI
- no important runtime error

## Responsive / Accessibility

Explicitly verify:
- 375px
- 768px
- 1024px
- 1440px

Ensure:
- usable navigation
- usable tables
- usable forms
- usable dialogs
- no important clipping
- semantic controls
- visible focus states
- keyboard usability
- labels
- accessible icon buttons
- adequate contrast
- reduced-motion support where relevant

## Validation During Work

After each meaningful checkpoint run relevant repository commands for:
- typecheck
- lint
- tests
- build

Fix regressions immediately.

When browser/E2E tooling exists, use it to verify real runtime behavior.

When possible, start the real application stack and validate representative flows against the real backend.

Check:
- console errors
- failed network requests
- broken routes
- broken assets
- auth redirects
- mutation refresh
- permissions

## Cross-Application Audit Before Finishing

Before declaring completion, compare:

1. backend capabilities
2. backend routes
3. backend permissions
4. frontend route inventory
5. generated API client
6. actual implemented UI
7. `docs/frontend-rebuild-status.md`

Find and fix:
- backend functionality with no UI
- frontend functionality with no working backend call
- missing roles
- missing pages
- dead controls
- incorrect permissions
- stale generated API integration
- broken cache invalidation
- broken pagination/search/filtering
- missing loading/error/empty states
- mobile layout failures
- remaining placeholders
- stale mocks
- TODO UI
- avoidable TypeScript suppressions

## Release Gate

Do not declare completion until:

- every discovered application route is covered
- every actual role is covered
- all important backend workflows are usable
- authentication works
- authorization works
- queries work
- mutations work
- forms work
- validation works
- responsive layouts work
- accessibility basics are satisfied
- runtime hostname/configuration is consistent
- no known release-blocking console/network problem remains
- TypeScript/typecheck passes
- lint passes
- relevant tests pass
- production build succeeds
- no fake/placeholder implementation replaces existing backend functionality

If you discover an in-scope problem that you can fix locally, fix it instead of stopping merely to report it.

Work autonomously through all phases until the release gate is satisfied.

At the very end provide a concise final report containing:

1. pages/routes completed
2. roles covered
3. major shared components/design system changes
4. backend integrations repaired/implemented
5. runtime/configuration changes
6. validation commands run and results
7. any remaining NON-release-blocking limitations

Do not claim completion if a release-blocking issue remains.
