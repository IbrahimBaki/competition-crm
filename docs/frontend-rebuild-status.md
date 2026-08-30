# Frontend Rebuild Status

This is the live release matrix for the production frontend rebuild. Roles are permission-derived; seeded roles are `administrator`, `manager`, `supervisor`, `agent`, and `viewer`, with custom roles supported.

Legend: `todo`, `partial`, `complete`, `blocked`.

| Role / identity | Route | Page | API / data | Permission | UI | Integration | Responsive | Tested |
|---|---|---|---|---|---|---|---|---|
| Staff | `/login` | Staff login | auth login/me | Public | partial | partial | partial | unit |
| Staff | `/login/two-factor` | Two-factor challenge | auth two-factor/me | Pending auth | partial | partial | partial | no |
| Staff | `/forgot-password` | Forgot password | auth password forgot | Public | todo | todo | todo | no |
| Staff | `/reset-password` | Reset password | auth password reset | Public | todo | todo | todo | no |
| Staff | `/invitations/:token` | Invitation acceptance | invitation accept | Public | todo | todo | todo | no |
| Authenticated staff | `/`, `/workspace` | Workspace | queues/tasks | Component permissions | partial | partial | partial | unit |
| Ticket viewers | `/tickets` | Ticket queues | tickets/queues/statuses/categories/views | `tickets.view.*` | partial | partial | partial | unit |
| Ticket creators | `/tickets/new` | New ticket | tickets create/categories | `tickets.create` | todo | todo | todo | no |
| Ticket viewers | `/tickets/:ticketId` | Ticket workbench | ticket/messages/history/links/watchers/SLA/tasks/AI | `tickets.view.*` + action keys | partial | partial | partial | unit |
| Customer viewers | `/customers` | Customers | customers list | `customers.view` | partial | partial | partial | unit |
| Customer creators | `/customers/new` | New customer | customers create | `customers.create` | todo | todo | todo | no |
| Customer viewers | `/customers/:customerId` | Customer 360 | customer/contacts/notes/files/timeline/duplicates/ERP | Customer action keys | partial | partial | partial | unit |
| Knowledge viewers | `/knowledge` | Knowledge articles | knowledge articles/search | `knowledge.articles.view` | todo | todo | todo | no |
| Knowledge creators | `/knowledge/new` | New article | knowledge create/render | `knowledge.articles.create` | todo | todo | todo | no |
| Knowledge viewers | `/knowledge/:articleId` | Article detail | article/versions | `knowledge.articles.view` | todo | todo | todo | no |
| Knowledge editors | `/knowledge/:articleId/edit` | Article editor | update/render/state/versions | Knowledge action keys | todo | todo | todo | no |
| Report viewers | `/reports` | Report catalogue | reports list | `reports.view.*` | partial | partial | partial | no |
| Report viewers | `/reports/:reportId` | Report detail | report/export | Report view/export keys | partial | partial | partial | no |
| Report viewers | `/dashboard` | Management dashboard | management report | `reports.view.*` | partial | partial | partial | no |
| Report schedulers | `/report-schedules` | Report schedules | schedules CRUD | `reports.schedule.manage` | todo | todo | todo | no |
| Staff | `/account` | Account security | me/2FA/preferences | Authenticated | todo | todo | todo | no |
| Organization viewers | `/admin` | Admin landing redirect | visible navigation | Any admin-area permission | partial | n/a | partial | no |
| Branch viewers | `/admin/branches` | Branches | branches CRUD/lifecycle | Branch view/manage | partial | complete | partial | unit |
| Branch viewers | `/admin/branches/:branchId` | Branch detail | branch/hours/holidays | Branch view/manage | partial | complete | partial | unit |
| Department viewers | `/admin/departments` | Departments | departments CRUD/lifecycle | Department view/manage | partial | complete | partial | unit |
| Department viewers | `/admin/departments/:departmentId` | Department detail | department CRUD | Department view/manage | partial | complete | partial | no |
| Team viewers | `/admin/teams` | Teams | teams CRUD/lifecycle | Team view/manage | partial | complete | partial | unit |
| Team viewers | `/admin/teams/:teamId` | Team detail | team CRUD | Team view/manage | partial | complete | partial | no |
| User admins | `/admin/users` | Users | users/invite/lifecycle/placement | Admin user keys | partial | partial | partial | no |
| Role admins | `/admin/roles` | Roles | roles CRUD | `admin.roles.manage` | partial | complete | partial | no |
| Role admins | `/admin/roles/:roleId` | Role detail | role/catalogue/membership | `admin.roles.manage` | partial | partial | partial | no |
| Catalogue admins | `/admin/ticket-catalogue` | Ticket catalogue | statuses/categories | Status/category manage | partial | partial | partial | no |
| SLA viewers | `/admin/sla-policies` | SLA policies | SLA CRUD | SLA view/manage | todo | todo | todo | no |
| Automation viewers | `/admin/automation-rules` | Automation rules | rules CRUD/executions | Automation keys | todo | todo | todo | no |
| Channel viewers | `/admin/channels` | Channel operations | email/forms/templates/chat | Channel keys | todo | todo | todo | no |
| Policy admins | `/admin/settings` | Security policy | auth policy | 2FA policy manage | partial | complete | partial | no |
| Audit viewers | `/admin/audit` | Audit log | audit logs | `admin.audit.view` | todo | todo | todo | no |
| Data-protection admins | `/admin/data-protection` | Retention and erasure | retention/users erase | Data-protection keys | todo | todo | todo | no |
| Integration admins | `/admin/integrations` | Integrations | tokens/webhooks/imports | Integration keys | todo | todo | todo | no |
| AI admins/users | `/admin/ai` | AI usage/suggestions | AI usage/suggestions | AI keys | todo | todo | todo | no |
| Portal visitor | `/portal/login` | Portal login | portal auth/me | Public | todo | todo | partial | no |
| Portal visitor | `/portal/register` | Portal registration | portal register | Public | todo | todo | partial | no |
| Portal visitor | `/portal/verify` | Portal verification | portal verify | Public | todo | todo | partial | no |
| Portal account | `/portal/tickets` | My tickets | portal tickets | Portal token | todo | todo | partial | isolation only |
| Portal account | `/portal/tickets/new` | New request | portal ticket create | Portal token | todo | todo | partial | no |
| Portal account | `/portal/tickets/:id` | Request detail | ticket/messages/feedback/files | Portal token/ownership | todo | todo | partial | no |
| Guest token holder | `/portal/track/:token` | Guest tracking | guest ticket/feedback | Tracking token | todo | todo | partial | no |
| Public | `/portal/help` | Help center | public knowledge | Public | todo | todo | partial | no |
| Public | `/portal/help/:articleId` | Help article | public article/feedback | Public | todo | todo | partial | no |
| Portal account | `/portal/account` | Portal account | portal me/logout | Portal token | todo | todo | todo | no |
| Public | `/forms/:formKey` | Public support form | public web form/submission | Public | todo | todo | todo | no |
| Public | `/forms/submissions/:trackingToken` | Submission tracking | public form status | Tracking token | todo | todo | todo | no |
| Public | `/chat` | Live chat | public chat session/messages | Public session | todo | todo | todo | no |
| All | `*` | Not found | None | None | partial | n/a | partial | no |

## Runtime baseline

- Canonical API: `http://competition-crm.azmsquad.localhost`
- Canonical SPA: `http://app.competition-crm.azmsquad.localhost`
- Staff auth: stateful Sanctum cookie.
- Portal auth: portal-scoped Sanctum bearer token.
- Current state: the checked-in Vite, Apache, cookie-domain, CORS, and environment examples consistently use this architecture. Browser verification against the canonical host still requires the host proxy/DNS runtime.

## Release gate

The rebuild is not complete while any matrix row remains `todo`, `partial`, or `blocked`, or while the repository release checks and browser/runtime verification have not passed.

## Current verification (2026-08-29)

- Frontend TypeScript: pass (`npm run typecheck`).
- Frontend lint: pass with zero warnings (`npm run lint`).
- Frontend tests: pass, 27 files and 106 tests (`npm test -- --run`). Existing React test `act(...)` and React Router v7 advisory output remains non-fatal.
- Frontend production build: pass (`npm run build`). The main bundle is 757.05 kB minified / 204.63 kB gzip and still emits Vite's 500 kB chunk advisory.
- Laravel tests in the documented `azm-php82` container: fail, 210 failed / 404 passed / 3 skipped / 2 risky after the release-blocker fixes. Representative groups are documented below and still prevent a production release claim.
- Running Laravel tests on the host is invalid because the host PHP lacks `pdo_mysql`; container execution is the canonical test path.

## Confirmed backend release blockers

1. The five integration blockers identified by the independent review are now implemented: staff 2FA defers authentication/token issuance until challenge completion; portal ticket create/reply/message listing use real ticket data; and public chat exposes active department discovery without internal fields.
2. Targeted auth, 2FA policy, portal auth, portal create/reply/isolation, portal feedback, and public chat tests pass. The encrypted recovery-code column was corrected to text storage, guarded 2FA attributes now persist correctly, MySQL duplicate feedback is mapped to the intended 409, and recovery codes are one-use.
3. The OpenAPI contract now includes public chat department discovery and the real chat-start payload, and Orval output was regenerated.
4. The Laravel suite remains red. The frontend rebuild must not conceal those failures or be marked production-ready until the remaining release-relevant failures are resolved and authenticated browser workflows can run against the canonical stack.

## Rebuild work completed in this pass

- Canonicalized the local SPA host as `app.competition-crm.azmsquad.localhost` and API host as `competition-crm.azmsquad.localhost` across Vite, Apache, environment examples, cookies, CORS, and documentation.
- Added semantic design tokens, accessible controls, dialogs, notifications, field errors, pagination, skeletons, responsive staff navigation, mobile navigation, skip links, and RTL-compatible layout behavior.
- Rebuilt staff and portal authentication surfaces, recovery and invitation routes, portal shell, portal ticket/help/account/tracking pages, and portal-owned async states that preserve staff/portal isolation.
- Added real staff creation routes for tickets and customers using the checked-in request validators.
- Added knowledge list/create/detail/edit routes using the bilingual backend contract.
- Connected SLA, automation, channels, audit, report schedules, integrations, AI operations, data protection, public web forms, submission tracking, and public chat surfaces to real endpoints.
- Corrected ticket scope gating so own/team/department users are not accidentally blocked by an `any`-only frontend check, and corrected ticket-catalogue routing to accept either status or category management permission.

The route matrix above remains authoritative: entries still marked `todo`, `partial`, or `blocked` require further work or backend completion. This document intentionally does not claim that the release gate has passed.

## Independent release-candidate review (2026-08-29)

Verdict: **FAIL**.

The implementation was re-compared from source against `routes/api.php`, Laravel request authorization and policies, `PermissionKey.php`, the React router, permission-filtered navigation, generated clients, and actual page components. Previous completion statements were not accepted as evidence.

Frontend defects fixed during this review:

- Channel administration routing now accepts every permission represented by its tabs, including web forms, templates, and live chat; it no longer incorrectly requires email-replay access.
- Knowledge list rows now deep-link to article detail and expose article creation only to users with `knowledge.articles.create`.
- Generic destructive actions now use an accessible application dialog instead of `window.confirm`.
- Dialogs now trap keyboard focus, support Escape, restore prior focus, and expose their description through `aria-describedby`.
- Route changes move focus to the staff main-content landmark.
- Staff account now exposes real authenticator enrollment, confirmation, recovery-code display, and password-confirmed disable flows.
- The authenticator QR is rendered as an image rather than injecting backend SVG markup into the DOM.
- Corrected resource-field mappings for audit logs, webhook deliveries, import runs, web forms, and live-chat sessions.
- Removed stale catalogue commentary that incorrectly described permission behavior as unknowable; authorization is permission-key driven.

Known release-blocking frontend gaps still present:

- Messaging templates remain read-only because the backend exposes only an index route.
- Knowledge category parent selection now uses UUIDs consistently at the API boundary; focused contract coverage verifies that numeric database IDs remain internal.
- Data-protection retention remains read-only because the backend exposes no update operation.
- The generic operations tables now provide server search and pagination, but several modules still lack endpoint-specific filters, sorting, and row detail navigation.
- The advertised `agent-browser` verifier is not installed. A fallback headless-Chrome DOM check confirms `/login` renders meaningful labelled controls without a Vite overlay, but representative authenticated role flows and responsive screenshots have not been completed.

Additional backend blockers confirmed independently:

- Resolved in the closure pass below: staff login now defers normal authentication when 2FA is enabled.
- Resolved in the closure pass below: portal ticket creation, replies, and message listing use real persisted data.
- Resolved in the closure pass below: public chat now discovers active departments through a public-safe endpoint.

## Release-blocker closure pass (2026-08-29)

Completed in this pass:

- Enforced the staff 2FA challenge before either a web session or personal access token is issued, including invalid TOTP, valid TOTP, and one-use recovery-code tests.
- Implemented real portal ticket creation, initial portal message persistence, reply persistence, paginated customer-visible conversation retrieval, isolation from internal notes, feedback submission, and authenticated attachment downloads.
- Added public-safe active-department discovery and connected the public chat selector to the regenerated API client.
- Added ticket status/category create, edit, and delete surfaces; automation rule create/edit/delete and execution history; report schedule create/edit/delete; API token issue/revoke, webhook CRUD, import creation/detail, webhook delivery detail; email replay; web-form CRUD; chat transcript/accept/transfer/reply/end; AI suggestion resolution and ticket AI actions; knowledge preview/lifecycle/version restore/category management; public article feedback; and staff personal-data erasure.
- Added reusable accessible resource dialogs with server validation, success/error feedback, invalidation, empty states, and responsive table behavior.
- Split generated API and vendor bundles. Production build now emits three JavaScript chunks (largest 409.10 kB) with no Vite chunk-size warning.

Still release-blocking:

- Authenticated browser E2E cannot be completed because the canonical Apache/API host is not reachable from the current host runtime; the Vite process is also isolated from follow-up browser commands in this execution environment.
- The knowledge-category parent contract is aligned on UUIDs and documented in OpenAPI. Several endpoint-specific filters and sorting controls remain incomplete.
- The full Pest suite remains red (210 failed, 404 passed, 3 skipped, 2 risky). Representative unrelated/pre-existing groups include unit tests that do not boot Laravel (`Model::$resolver`/container bindings missing), invalid database fixtures (`value_normalised`, `invited_by_uuid`), and feature tests that create permissionless users then expect permission-protected endpoints to succeed. Ticket status catalogue tests are an example of the last group.
- The release-critical backend selection passes (31 tests, 86 assertions). Frontend typecheck, lint, all 106 tests, and the production build pass; `git diff --check` also passes after normalizing generated-client whitespace.
- The release gate therefore remains **FAIL** because authenticated browser E2E has not been executed and the full Laravel suite remains red.

## Continued browser closure pass

- Real Chrome 145 authentication and network capture now run against the canonical Vite and Laravel hosts through the DevTools protocol; the installed ChromeDriver 137 is incompatible and is not used as evidence.
- Verified real 200-backed desktop rendering for tickets, customers, report schedules, account, branches, departments, teams, users, catalogue, SLA policies, automation, integrations, audit, and AI suggestions. These observations do not by themselves complete each route's mutation/responsive matrix.
- Repaired real-browser blockers in report authorization/catalogue serialization/filter transport/export URL, role-list resource mapping, inbound-email collection handling, data-protection authorization/render mapping, and AI-suggestion authorization.
- The host filesystem reached 100% during browser verification. A disposable 212 MB Chrome profile was removed, but later Chrome sessions still stalled at the application loading boundary. Remaining route/action/responsive checks stay FAIL until valid browser evidence is captured.
- Focused reporting tests pass (6 tests, 7 assertions). A broader authorization test selection still has two stale branch-create fixtures returning 422 because they send `display_name` instead of the active request fields; this is recorded for full-suite triage and was not hidden by weakening authorization.
- The fresh full Pest run completed with 205 failed, 414 passed, 3 skipped, 2 risky, and 1967 assertions. Failure groups remain dominated by frameworkless model tests, stale factories/fixtures, and permissionless protected-route expectations, but active-domain groups also remain red; the backend release gate is therefore still FAIL.

The release gate must remain failed until all frontend gaps above are implemented, browser role coverage is executed, backend blockers are resolved, and both frontend and Laravel quality gates pass.
