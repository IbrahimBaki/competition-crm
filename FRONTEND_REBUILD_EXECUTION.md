# FRONTEND_REBUILD_EXECUTION.md

## Objective

Deliver a fully rebranded, production-ready React frontend integrated with the existing Laravel backend.

This file is the execution checklist after the repository audit is complete.

---

## Phase 1 — Freeze Audit Output

Before coding:

- [ ] Save/update the discovered route inventory
- [ ] Save/update actual roles and permissions
- [ ] Save/update frontend-to-backend API mapping
- [ ] Record broken/missing pages
- [ ] Record current auth flow
- [ ] Record runtime hostname/config issues
- [ ] Record design direction from UI UX Pro Max

Create/update:

`docs/frontend-rebuild-status.md`

The status file must include a coverage matrix for every discovered page.

Recommended columns:

| Role | Route | Page | API/Data | Permission | UI | Integration | Responsive | Tested |
|---|---|---|---|---|---|---|---|---|

Do not omit routes using phrases like "similar pages".

---

## Phase 2 — Design System + Shared Foundation

- [ ] Read `design-system/MASTER.md`
- [ ] Normalize design tokens
- [ ] Build/refactor application shell
- [ ] Sidebar
- [ ] Top navigation
- [ ] Mobile navigation
- [ ] Page header
- [ ] Breadcrumbs
- [ ] Buttons
- [ ] Inputs/selects/textareas
- [ ] Form error component
- [ ] Cards
- [ ] Tables
- [ ] Badges
- [ ] Tabs
- [ ] Dialogs
- [ ] Dropdowns
- [ ] Alerts/toasts
- [ ] Skeletons/loading states
- [ ] Empty states
- [ ] Error states
- [ ] Pagination
- [ ] Filters/search
- [ ] Confirmation dialog

Verify typecheck/lint/build after foundation work.

---

## Phase 3 — Runtime + Authentication

- [ ] Reconcile local hostnames
- [ ] Verify Vite allowedHosts
- [ ] Verify frontend API base URL
- [ ] Verify proxy/Apache
- [ ] Verify Laravel APP_URL
- [ ] Verify CORS
- [ ] Verify session/cookie domain
- [ ] Verify Docker/local environment
- [ ] Establish one documented local run path

Authentication:
- [ ] Staff login
- [ ] Portal login
- [ ] Logout
- [ ] Current user
- [ ] Session expiry
- [ ] 401 handling
- [ ] 403 handling
- [ ] Route guards
- [ ] Role-aware navigation

Verify with real backend when possible.

---

## Phase 4 — Rebuild Every Application Page

Process every route discovered during audit.

For EACH page:

- [ ] New UI follows `design-system/MASTER.md`
- [ ] Correct route
- [ ] Correct role visibility
- [ ] Correct permissions
- [ ] Real backend query
- [ ] Real backend mutations
- [ ] Loading state
- [ ] Empty state
- [ ] Error state
- [ ] Form validation if applicable
- [ ] Server validation errors if applicable
- [ ] Success feedback
- [ ] Mutation cache invalidation/refetch
- [ ] Responsive
- [ ] Keyboard/focus behavior
- [ ] No fake data
- [ ] No dead buttons
- [ ] No placeholder screens

Update `docs/frontend-rebuild-status.md` continuously.

Never mark a page complete based only on rendering.

---

## Phase 5 — Cross-Application Functional Audit

Compare:

1. Laravel routes/controllers/capabilities
2. authorization rules
3. frontend routes
4. generated API client
5. implemented UI
6. `docs/frontend-rebuild-status.md`

Find and fix:
- [ ] backend functionality missing from UI
- [ ] dead frontend actions
- [ ] wrong endpoints
- [ ] stale generated API usage
- [ ] missing mutations
- [ ] bad cache invalidation
- [ ] broken pagination
- [ ] broken filters/search
- [ ] permission leaks
- [ ] inaccessible valid pages
- [ ] missing role flows

---

## Phase 6 — Responsive + Accessibility Pass

Explicitly check:
- [ ] 375px
- [ ] 768px
- [ ] 1024px
- [ ] 1440px

Check:
- [ ] no important horizontal overflow
- [ ] tables remain usable
- [ ] dialogs fit viewport
- [ ] navigation works on mobile
- [ ] forms remain usable
- [ ] visible focus states
- [ ] keyboard navigation
- [ ] labels
- [ ] contrast
- [ ] accessible icon buttons
- [ ] reduced-motion support where relevant

---

## Phase 7 — Runtime Verification

If browser/E2E tooling exists:

- [ ] start real stack
- [ ] authenticate as each important role
- [ ] visit every major route class
- [ ] perform representative create/update/delete flows
- [ ] check console
- [ ] check network failures
- [ ] verify redirects
- [ ] verify 401/403 behavior
- [ ] verify mutation refresh behavior
- [ ] verify assets

Do not treat compilation as runtime verification.

---

## Phase 8 — Cleanup

- [ ] remove verified dead frontend code
- [ ] remove stale mocks
- [ ] remove obsolete styles
- [ ] consolidate duplicate components
- [ ] remove temporary debug code
- [ ] remove placeholder copy
- [ ] remove TODO UI
- [ ] remove unnecessary TypeScript suppressions

Do not delete uncertain code until usage is traced.

---

## Phase 9 — Release Gate

Run repository-equivalent commands for:

- [ ] install/dependency integrity
- [ ] typecheck
- [ ] lint
- [ ] unit tests
- [ ] integration tests
- [ ] E2E tests if present
- [ ] production build

Then verify:

- [ ] all discovered routes covered
- [ ] all actual roles covered
- [ ] auth works
- [ ] authorization works
- [ ] real API integration works
- [ ] no release-blocking console errors
- [ ] no release-blocking network failures
- [ ] no fake data where real backend exists
- [ ] no placeholder screens
- [ ] local environment is reproducible
- [ ] production build succeeds

Only then may the rebuild be declared complete.
