# React Functional Audit

Live working document for the React-to-Laravel functional review performed against:

- Frontend: `http://app.competition-crm.azmsquad.localhost:5174`
- API: `http://competition-crm.azmsquad.localhost/api/v1`
- Staff authentication: stateful Sanctum cookie
- Portal authentication: portal bearer token

Legend: `PASS`, `FAIL`, `N/A`. A `FAIL` includes both a confirmed defect and a workflow not yet browser-verified; nothing unverified is treated as complete.

| Role | Route | Page Load | Main Query | Create | Update | Delete | Other Actions | Search | Filters | Sort | Pagination | Detail Navigation | Skeleton | Error State | Responsive | Browser Tested |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Public staff | `/login` | PASS | N/A | N/A | N/A | N/A | PASS | N/A | N/A | N/A | N/A | N/A | N/A | PASS | PASS | PASS |
| Pending staff | `/login/two-factor` | FAIL | N/A | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | PASS | FAIL | FAIL |
| Public staff | `/forgot-password` | PASS | N/A | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | PASS | PASS | PASS |
| Public staff | `/reset-password` | PASS | N/A | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | PASS | PASS | PASS |
| Invitee | `/invitations/:token` | PASS | N/A | PASS | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | PASS | PASS | FAIL |
| Staff | `/` | PASS | PASS | PASS | N/A | N/A | PASS | N/A | N/A | N/A | N/A | PASS | FAIL | PASS | PASS | PASS |
| Staff | `/workspace` | PASS | PASS | PASS | N/A | N/A | PASS | N/A | N/A | N/A | N/A | PASS | FAIL | PASS | PASS | PASS |
| Ticket viewers | `/tickets` | PASS | PASS | N/A | N/A | N/A | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | PASS | PASS | PASS |
| Ticket creators | `/tickets/new` | PASS | PASS | PASS | N/A | N/A | N/A | N/A | N/A | N/A | N/A | PASS | FAIL | PASS | PASS | PASS |
| Ticket viewers | `/tickets/:ticketId` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| Customer viewers | `/customers` | FAIL | FAIL | N/A | N/A | N/A | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Customer creators | `/customers/new` | FAIL | FAIL | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | N/A | FAIL | FAIL | PASS | FAIL | FAIL |
| Customer viewers | `/customers/:customerId` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| Knowledge viewers | `/knowledge` | PASS | PASS | N/A — article creation uses `/knowledge/new` | N/A — article editing uses the detail edit route | N/A — backend exposes no article/category delete endpoint | PASS | PASS | N/A — backend exposes no article-list filters beyond search | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| Knowledge creators | `/knowledge/new` | PASS | PASS | PASS | N/A — create-only route | N/A — backend exposes no article delete endpoint | PASS | N/A — form route has no list | N/A — form route has no list | N/A — form route has no list | N/A — form route has no list | PASS | N/A — form renders synchronously while category options load in-place | PASS | PASS | PASS |
| Knowledge viewers | `/knowledge/:articleId` | PASS | PASS | N/A — creation uses `/knowledge/new` | PASS | N/A — backend exposes no article delete endpoint | PASS | N/A — detail route has no search | N/A — detail route has no filters | N/A — detail route has no sorting | N/A — versions endpoint is intentionally unpaginated | N/A — already on the article detail route | PASS | PASS | PASS | PASS |
| Knowledge editors | `/knowledge/:articleId/edit` | PASS | PASS | N/A — edit-only route | PASS | N/A — backend exposes no article delete endpoint | PASS | N/A — form route has no search | N/A — form route has no filters | N/A — form route has no sorting | N/A — form route has no pagination | PASS | PASS | PASS | PASS | PASS |
| Staff | `/account` | PASS | PASS | N/A | FAIL | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | PASS | FAIL |
| Report viewers | `/reports` | FAIL | FAIL | N/A | N/A | N/A | FAIL | PASS | PASS | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Report viewers | `/reports/:reportId` | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Report viewers | `/dashboard` | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | FAIL | N/A | N/A | FAIL | FAIL | PASS | FAIL | FAIL |
| Report schedulers | `/report-schedules` | FAIL | FAIL | FAIL | FAIL | FAIL | N/A | FAIL | FAIL | FAIL | FAIL | N/A | PASS | PASS | PASS | PASS |
| Admin users | `/admin` | PASS | N/A | N/A | N/A | N/A | PASS | N/A | N/A | N/A | N/A | PASS | N/A | PASS | PASS | PASS |
| Branch viewers | `/admin/branches` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Branch viewers | `/admin/branches/:branchId` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| Department viewers | `/admin/departments` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Department viewers | `/admin/departments/:departmentId` | FAIL | FAIL | N/A | FAIL | FAIL | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Team viewers | `/admin/teams` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Team viewers | `/admin/teams/:teamId` | FAIL | FAIL | N/A | FAIL | FAIL | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| User admins | `/admin/users` | FAIL | FAIL | FAIL | FAIL | N/A | FAIL | PASS | PASS | FAIL | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| Role admins | `/admin/roles` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Role admins | `/admin/roles/:roleId` | FAIL | FAIL | N/A | FAIL | FAIL | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Catalogue admins | `/admin/ticket-catalogue` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| SLA viewers | `/admin/sla-policies` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| Automation viewers | `/admin/automation-rules` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Channel viewers | `/admin/channels` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Policy admins | `/admin/settings` | FAIL | FAIL | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Audit viewers | `/admin/audit` | FAIL | FAIL | N/A | N/A | N/A | FAIL | PASS | PASS | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Data protection admins | `/admin/data-protection` | FAIL | FAIL | N/A | N/A | N/A | FAIL | PASS | PASS | FAIL | FAIL | N/A | FAIL | PASS | FAIL | FAIL |
| Integration admins | `/admin/integrations` | FAIL | FAIL | FAIL | FAIL | FAIL | FAIL | PASS | PASS | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| AI admins | `/admin/ai` | FAIL | FAIL | N/A | FAIL | N/A | FAIL | PASS | PASS | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Portal visitor | `/portal/login` | PASS | N/A | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | PASS | N/A | PASS | PASS | FAIL |
| Portal visitor | `/portal/register` | PASS | N/A | PASS | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | PASS | N/A | PASS | PASS | FAIL |
| Portal visitor | `/portal/verify` | PASS | N/A | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | PASS | N/A | PASS | PASS | FAIL |
| Guest token holder | `/portal/track/:token` | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Public | `/portal/help` | FAIL | FAIL | N/A | N/A | N/A | FAIL | PASS | PASS | FAIL | FAIL | FAIL | FAIL | PASS | FAIL | FAIL |
| Public | `/portal/help/:slug` | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Portal customer | `/portal/tickets` | FAIL | FAIL | N/A | N/A | N/A | FAIL | PASS | PASS | PASS | PASS | PASS | FAIL | PASS | FAIL | FAIL |
| Portal customer | `/portal/tickets/new` | FAIL | FAIL | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | N/A | FAIL | FAIL | PASS | FAIL | FAIL |
| Portal customer | `/portal/tickets/:id` | FAIL | FAIL | FAIL | N/A | N/A | FAIL | N/A | N/A | N/A | PASS | N/A | FAIL | PASS | FAIL | FAIL |
| Portal customer | `/portal/account` | FAIL | FAIL | N/A | FAIL | N/A | PASS | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Public | `/forms/:formKey` | FAIL | FAIL | FAIL | N/A | N/A | N/A | N/A | N/A | N/A | N/A | FAIL | FAIL | PASS | FAIL | FAIL |
| Public | `/forms/submissions/:trackingToken` | FAIL | FAIL | N/A | N/A | N/A | FAIL | N/A | N/A | N/A | N/A | N/A | FAIL | PASS | FAIL | FAIL |
| Public | `/chat` | FAIL | FAIL | PASS | N/A | N/A | FAIL | N/A | N/A | N/A | PASS | N/A | FAIL | PASS | FAIL | FAIL |
| All | `*` | PASS | N/A | N/A | N/A | N/A | PASS | N/A | N/A | N/A | N/A | N/A | N/A | PASS | PASS | PASS |

## Confirmed runtime findings

1. Staff login and the administrator workspace load against the real backend.
2. Development StrictMode aborts the first signal-aware TanStack Query requests and immediately replaces them. This is expected development-only behavior. The separate direct auth bootstrap duplication was a bug and is deduplicated.
3. `/tickets` GETs all returned 200, but the page crashed by rendering a bilingual department-name object as a React child. Department names are now localized before rendering.
4. Knowledge category/article GETs returned 500 because policies called nonexistent `User::hasPermission()`. Knowledge, automation, and API-token policies now use registered Laravel permission gates.
5. Report schedules returned 403 because the controller authorized nonexistent model-policy abilities. It now authorizes `reports.schedule.manage` directly.
6. Create Task initially failed because `CreateAgentTask` checked a nonexistent `is_active` attribute. It now calls `User::isActive()`. A real agent browser submission sent `2026-08-30T16:30:00+03:00`, returned 201, and closed the dialog.
7. Create Ticket exposed and repaired four backend integration defects: nonexistent customer UUID lookup, missing model imports, SLA enum double-conversion, and TicketResource treating the lifecycle enum as a status-definition model/unloaded SLA relation as a collection. A real browser submission returned 201 and navigated to the created ticket detail URL.
8. A real administrator browser request to `GET /reports` first returned 403 because `ReportController` authorized a policy class as a model. Direct scope authorization now accepts `reports.view.any`, `reports.view.department`, or `reports.view.own`; focused tests pass for allowed and denied users.
9. The report catalogue then returned 500 because an array was passed to the paginator-only response helper, followed by a React crash because report-definition objects serialized without `key`, `permission`, or `columns`. The endpoint now returns six explicit definition DTOs. Real Chrome renders all six report cards and navigates to `/reports/ticket_volume` and `/dashboard` without a route error.
10. Report detail filters were computed but discarded by the frontend adapter. `fetchReport` now sends every whitelisted `ReportQueryRequest` parameter, and export no longer duplicates `/v1` in its URL. Date-filter submission and export still require browser action verification, so the report-detail matrix remains FAIL.
11. Role list pagination returned raw models even though detail responses used `RoleResource`. Real Chrome showed zero permissions and `/admin/roles/` for every row. The list now uses the resource collection; Chrome verified UUID detail links and the real permission counts (111/82/55/37/2).
12. Inbound email returned 500 because it called the current `CollectionQuery` API statically with an obsolete signature. It now declares supported search, state filtering, sorting, and standard pagination, and returns the shared paginated envelope. Final browser rendering remains pending because Chrome later stalled under host disk exhaustion.
13. Data-protection retention and AI suggestions returned 403 to an administrator holding their explicit keys because the controllers authorized nonexistent model-policy abilities. Both now authorize their registered permission keys. Chrome verified AI suggestions returns 200 and renders its empty state. Retention returned 200, exposing a frontend mapping crash that is now corrected to render each retention class and cutoff; final browser recheck remains pending.
14. Browser runs use installed Chrome 145 over DevTools because the available ChromeDriver only supports Chrome 137. Repeated Vite runs filled the host filesystem; after removing only the disposable 212 MB E2E profile, the filesystem still reported 100% usage and later Chrome sessions stalled on the loading boundary. No stalled run is counted as route evidence.
