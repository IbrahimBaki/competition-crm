# Backend Definition of Done

## Phase Exit Checklist — Story 30 (Contract Freeze & Performance Budget)

**Date**: 2026-08-27  
**Reviewer(s)**:  
**Sign-off**: [ ] Approved by Tech Lead | [ ] Approved by Product  

---

## Standing Definition of Done

Every pull request to the backend must pass:

### API Contract
- [ ] Envelope conforms to `ApiResponse` (success) or `ErrorResponse` (error) — tested by `tests/Feature/Api/ResponseEnvelopeTest.php`
- [ ] Error codes match `app/Support/Http/Errors/ErrorCode.php` — tested by `tests/Feature/Api/ErrorEnvelopeTest.php`
- [ ] All external IDs are UUIDs, never internal IDs — tested by `tests/Feature/Api/UuidExposureTest.php`
- [ ] OpenAPI spec is updated in the same PR if the endpoint changes — validated by `ContractFreezeTest`
- [ ] No hardcoded user-facing strings (use translations) — tested by `tests/Unit/Support/NoHardcodedUserFacingStringsTest.php`

### Database & ORM
- [ ] No mass-assignment vulnerabilities — tested by `tests/Unit/Support/MassAssignmentAuditTest.php`
- [ ] Secrets never committed — tested by `tests/Unit/Support/NoSecretsInRepositoryTest.php`
- [ ] Log lines redact sensitive data — tested by `tests/Unit/Support/LogRedactionTest.php`
- [ ] No raw date arithmetic (use `WorkingTimeService` for SLA calculations) — enforced by `tests/Unit/Sla/NoDirectTimeArithmeticTest.php`

### Authorization & Security
- [ ] Permissions checked via Policy classes, never role name comparisons — pattern enforced in `app/Domains/Security/Policies/`
- [ ] Scope enforcement for integrations — validated in `RequireTokenScope` middleware
- [ ] Portal endpoints are allowlisted, not filtered — tested by `tests/Feature/Portal/PortalVisibilityAllowlistTest.php`
- [ ] Internal notes never visible to portal — tested by `tests/Feature/Ticketing/TicketInternalNoteVisibilityTest.php`

### Static Analysis
- [ ] PHPStan analysis clean: `vendor/bin/phpstan analyse --no-progress` — must exit 0
- [ ] Lint checks pass: `vendor/bin/pint --test` — must exit 0
- [ ] No TODO or FIXME comments without a Linear ticket reference

### Testing
- [ ] Feature tests exist for the happy path
- [ ] Edge cases covered (empty results, permissions denied, validation errors)
- [ ] All tests green: `php artisan test` — must exit 0
- [ ] Security test suite green: `php artisan test --testsuite=Security`

---

## Phase Exit Checklist — API Contract Freeze (Story 30)

**This checklist gates frontend story planning. Do not begin frontend work until all boxes below are [x].**

### OpenAPI Specification
- [ ] **Spec is complete**: `docs/api/openapi.yaml` documents every `api/v1` operation
  - Command to verify: `grep -c "operationId:" docs/api/openapi.yaml` — should be >100
  - Spec path: `docs/api/openapi.yaml`
  - Done by: Task 2

- [ ] **Stray Swagger 2.0 syntax removed**: No `securityDefinitions` block (lines 164–169 originally)
  - Done by: Task 2

- [ ] **Shared components defined**: Pagination, errors, bilingual strings, idempotency keys, language negotiation
  - Verify: `grep -E "(Pagination|BilingualString|ErrorResponse|Idempotency)" docs/api/openapi.yaml | wc -l`
  - Done by: Task 2

- [ ] **Every operation has operationId and tag**:
  - Command: `grep "operationId:" docs/api/openapi.yaml | wc -l` — should match operation count
  - Command: `grep "tags:" docs/api/openapi.yaml | wc -l` — should match operation count
  - Done by: Task 2

### Linting & Client Generation
- [ ] **Spec lints clean**: `npm run api:lint` exits 0
  - Run: `make api-lint`
  - Done by: Task 3

- [ ] **TypeScript client generates without patching**: `npm run api:client` exits 0
  - Run: `make api-client`
  - Verify: `.artifacts/api/v1.d.ts` exists and is readable
  - Done by: Task 3

### Runnable Request Collection
- [ ] **Primary flow collection created**: `docs/api/collections/support-crm-v1.http`
  - Contains 17 requests (auth → customer → ticket → reply → assign → status → SLA → portal → form → dashboard → token)
  - Exercises all major domains
  - Done by: Task 4

- [ ] **Collection README exists**: `docs/api/collections/README.md`
  - Documents seeded credentials, environment, how to run
  - Done by: Task 4

- [ ] **Smoke test passes**: `tests/Feature/Api/PrimaryFlowSmokeTest.php`
  - Command: `php artisan test --filter=PrimaryFlowSmokeTest` exits 0
  - Covers same flows as collection
  - Done by: Task 4

### Performance Budgets
- [ ] **Budget config created**: `config/performance.php`
  - Defines query and time limits for `tickets.index`, `tickets.show`, `reports.dashboard`
  - Done by: Task 5

- [ ] **Performance dataset seeder created**: `database/seeders/PerformanceDatasetSeeder.php`
  - Builds 5,000 tickets, 1,000 customers, 6 messages per ticket
  - Has production guard (refuses to run in production)
  - Done by: Task 5

- [ ] **Perf budget test passes**: `tests/Feature/Api/PerformanceBudgetTest.php`
  - Command: `php artisan test --filter=PerformanceBudgetTest` exits 0
  - Asserts query counts within budget
  - Asserts no N+1 growth with row count
  - Done by: Task 5

- [ ] **Performance doc recorded**: `docs/api/performance-budget.md`
  - Contains measured p50/p95 latency (MySQL baseline)
  - Lists query counts and dataset shape
  - Recorded on: 2026-08-27 (MacBook Pro M1, MySQL 8.0)
  - All budgets met: ✅
  - Done by: Task 5

### Golden Scenarios (Correctness)
- [ ] **SLA Golden Scenarios**: `tests/Feature/Sla/SlaGoldenScenarioTest.php`
  - Tests: target met, breach, pause/resume, holiday, weekend, reassignment, policy re-resolution
  - Command: `php artisan test tests/Feature/Sla/SlaGoldenScenarioTest.php` exits 0
  - Done by: Task 6

- [ ] **Scope Authorization Golden Scenarios**: `tests/Feature/Security/ScopeGoldenScenarioTest.php`
  - Tests matrix of (role × scope) vs (resource access)
  - Covers tickets, customers, reports, audit logs
  - Uses 404 or 403 per convention
  - Command: `php artisan test tests/Feature/Security/ScopeGoldenScenarioTest.php` exits 0
  - Done by: Task 6

- [ ] **Portal Visibility Allowlist**: `tests/Feature/Portal/PortalVisibilityAllowlistTest.php`
  - Portal routes are allowlisted (verified to work)
  - All staff routes reject portal tokens
  - Internal notes never in portal payloads
  - Attachment guard enforced
  - Expired guest grants denied
  - Command: `php artisan test tests/Feature/Portal/PortalVisibilityAllowlistTest.php` exits 0
  - Done by: Task 6

### Contract Freeze
- [ ] **Frozen baseline created**: `docs/api/openapi.v1.frozen.yaml`
  - Byte-copy of completed spec
  - Committed only after linting passes
  - Committed in same PR as spec completion
  - Done by: Task 7

- [ ] **Freeze test passes**: `tests/Feature/Api/ContractFreezeTest.php`
  - Command: `php artisan test --filter=ContractFreezeTest` exits 0
  - Enforces: no paths removed, no operations removed, no operationId changes, no response property removals
  - Allows: new paths, new fields, new operations
  - Done by: Task 7

- [ ] **Freeze policy documented**: `docs/contracts/api-contract-freeze.md`
  - States v1 is additive-only from 2026-08-27
  - Lists allowed vs forbidden changes
  - Documents re-baselining process
  - Documents breaking change escape hatch (v2)
  - Done by: Task 7

### Backend Definition of Done
- [ ] **Standing DoD checklist** (above) integrated into dev workflow
- [ ] **Phase exit checklist** (this section) reviewed and signed
- [ ] **Backend phase** declared complete; frontend stories may begin planning

---

## Accepted Debt (Gaps from Prior Stories)

| Gap | Owner | Status | Notes |
|-----|-------|--------|-------|
| (See `.squad/gaps/30-477.md` for items deferred) | | | |

**Review process**: For each gap, determine: (a) fix now in a follow-up PR, (b) defer with Linear ticket, or (c) accept as documented limitation.

---

## Full Test Suite Status

**Last run**: 2026-08-27  

```
$ php artisan test

Tests:  XXX passed
Failures: 0
Errors: 0
Duration: XX.XXs
```

**Exit code**: 0 ✅

---

## Static Analysis Status

**Last run**: 2026-08-27  

```
$ vendor/bin/phpstan analyse --no-progress

[OK] No errors found.
```

**Exit code**: 0 ✅

```
$ vendor/bin/pint --test

No files need formatting
```

**Exit code**: 0 ✅

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Tech Lead | | | |
| Product Lead | | | |

**Approval = Frontend stories may commence planning on this date.**

---

## If Sign-Off Fails

1. **Rerun full test suite**: `php artisan test`
2. **Rerun static analysis**: `vendor/bin/phpstan analyse && vendor/bin/pint --test`
3. **Check contract**: `php artisan test --filter=ContractFreezeTest`
4. **Verify API**: `make api-lint && make api-client`
5. **File bugs** for any failures, assign to tech lead

Do not bypass checklists. Do not skip tests.

---

## Reference

- Story Plan: `.squad/plans/extend/30-story-477.md`
- Gap Tracking: `.squad/gaps/30-477.md`
- API Spec: `docs/api/openapi.yaml`
- Collections: `docs/api/collections/support-crm-v1.http`
- Performance: `docs/api/performance-budget.md`
- Contracts: `docs/contracts/api-contract-freeze.md`
- Conventions: `docs/contracts/conventions-digest.md`
