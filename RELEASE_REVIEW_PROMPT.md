# RELEASE_REVIEW_PROMPT.md

Perform a final release-candidate audit of the entire frontend.

Do not add unrelated new product features.

Compare:
1. Laravel backend routes and capabilities
2. backend authorization rules
3. frontend route inventory
4. generated API client
5. actual implemented frontend
6. `docs/frontend-rebuild-status.md`
7. `design-system/MASTER.md`

Aggressively search for incomplete work and regressions.

Look for:
- backend functionality with no frontend surface
- frontend actions disconnected from backend
- missing pages
- missing roles
- wrong permissions
- inaccessible valid pages
- broken navigation
- dead buttons
- forms that fail
- server validation not displayed
- queries using wrong parameters
- mutations with stale UI afterward
- missing cache invalidation
- broken pagination
- broken filtering/search
- missing loading states
- missing empty states
- missing error states
- mobile overflow
- broken dialogs/dropdowns
- inconsistent design system usage
- inaccessible controls
- console errors
- failed network requests
- broken assets
- TODOs
- placeholder content
- stale mocks
- avoidable TypeScript suppressions
- hostname/runtime inconsistencies

Fix every valid in-scope issue you find.

Then run the complete repository-equivalent quality gate:

- typecheck
- lint
- tests
- integration/E2E tests if available
- production build

If browser/E2E tooling is available, verify representative flows for all major roles against the real backend.

Update `docs/frontend-rebuild-status.md`.

Do not declare release readiness while any known release-blocking frontend issue remains.

Finish with:
- PASS/FAIL release verdict
- commands run
- remaining non-blocking issues only
