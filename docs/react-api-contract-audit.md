# React API Contract Audit

This is the live contract ledger for React usage versus the generated client, OpenAPI, Laravel routes, requests, controllers/actions, and resources.

## Runtime contract

| Concern | Frontend | Backend | Status |
|---|---|---|---|
| Base URL | `VITE_API_BASE_URL=http://competition-crm.azmsquad.localhost` | Apache canonical host | PASS |
| Prefix | `/api/v1` | `Route::prefix('v1')` under `/api` | PASS |
| Staff auth | `withCredentials`, XSRF cookie/header | stateful Sanctum | PASS |
| Portal auth | bearer token only for `/portal/*` | `auth:portal`, `portal.auth` | PASS |
| Success envelope | `data`, optional `meta`/`links` | API resources and `ApiResponse` | PASS with legacy meta normalization |
| Error envelope | normalized code/message/field errors | API exception renderer | FAIL: some authorization failures still emit Laravel debug envelopes |

## Confirmed mismatches and repairs

| Surface | Frontend / generated contract | Actual Laravel contract | Resolution |
|---|---|---|---|
| Agent task `due_at` | ISO date-time from `datetime-local` | Original `date_format:c` rejected valid ISO-8601; accepted literal is `Y-m-d\\TH:i:sP` | Backend validation corrected; shared workspace formatter emits numeric offset; regression tests cover create/update payloads |
| Agent task owner | public user UUID | `exists:users,uuid`, then `User::isActive()` | PASS: real agent create returned 201 |
| Agent task state | generated `pending/in_progress/completed/cancelled` | `open/in_progress/done/cancelled` | workspace wire adapter maps the real enum |
| Ticket list departments | typed `name: string` | bilingual `{ar,en}` | localized with `pickBilingual` before rendering |
| Ticket create department | field named `department_uuid` | `exists:departments,id`; department public `id` is the UUID primary key | Field naming is misleading but wire value is public UUID; no internal numeric ID is exposed |
| Ticket message | generated `visibility` | `channel`, `is_internal` | ticket wire adapter sends Laravel request fields |
| Ticket status | generated `status_id` | `status`, optional `reason` | ticket wire adapter sends Laravel request fields |
| Ticket assignment | generated `user_id` | `assignee_uuid`, optional `version` | ticket wire adapter sends UUID contract |
| Ticket transfer agent | generated `user_id` | `user_uuid`, optional `version` | ticket wire adapter sends UUID contract |
| Ticket transfer department | incomplete generated body | `department_id`, `keep_assignee`, `version` | ticket wire adapter supplies real body |
| Ticket links | generated `related_ticket_id/link_type` | `target/relation` | ticket wire adapter supplies real body |
| Customer timeline | generated `page/per_page` | controller reads `limit/before` | FAIL: secondary panel still casts around mismatch |
| Knowledge category parent | frontend uses public category UUID | requests validate `knowledge_categories.uuid`; store/update resolve it to the internal parent key | PASS: real browser child-category create/update returned 201/200 without leaking internal IDs |
| Knowledge article category | frontend sends the selected public category UUID | requests validate UUID and actions resolve the internal foreign key | PASS: real browser article create/update returned 201/200 with the selected category |
| Knowledge article indexing | bilingual values are `BilingualString` objects after model casts | indexer incorrectly used array offsets | PASS: indexer uses value-object properties; real article create returned 201 |
| Knowledge lifecycle authorization | state controller helper shadowed and recursively called the framework `authorize` method | publish/archive require their policy abilities | PASS: helper alias corrected; browser review/publish/archive and version restore returned 200 |
| Knowledge rendered preview | renderer rejects unpublished articles with `article_not_published` | detail page previously queried it for every state | PASS: preview query is enabled only for published articles and supports cancellation |
| Knowledge/automation/API-token authorization | policies called missing `User::hasPermission()` | permissions are registered as Laravel Gate abilities | policies changed to `$user->can(...)` |
| Report-schedule authorization | controller called unregistered model-policy abilities | permission key is `reports.schedule.manage` | controller authorizes the registered ability directly |
| Public chat start | stale generated name/email-only body | department UUID required; optional display/contact/locale/token | OpenAPI updated and client regenerated |
| Report authorization | controller passed `ReportPolicy::class` through model-policy authorization | report access is scope-key based (`any`, `department`, `own`) | PASS: focused allowed/denied endpoint tests and administrator browser GET |
| Report catalogue | frontend expects an array of `{key, permission, columns}` | registry returned unserializable service objects through a paginator-only response helper | PASS: explicit DTO list; six real cards render in Chrome |
| Report query filters | adapter whitelisted filters but called generated `getReport(id)` without them | `ReportQueryRequest` requires date range and supports timezone/relationship/priority/channel/tag filters | PASS by adapter fix; browser form submission remains pending |
| Report export URL | adapter requested `/v1/reports/...` below an API client already rooted at `/api/v1` | route is `/api/v1/reports/{report}/export` | PASS by adapter correction; browser export remains pending |
| Role list resources | paginated index emitted raw models | show/create/update emit `RoleResource` with public UUID and permissions | PASS: index now uses the same resource; UUID links verified in Chrome |
| Inbound email collection | obsolete static `CollectionQuery::paginate($query, $request)` caused 500 | current helper requires a request/spec instance and standard page envelope | PASS at controller contract; final browser render pending |
| Data-protection retention authorization | controller authorized `view` on `User::class` | registered key is `dataprotection.retention.view` | PASS: administrator browser GET returned 200 |
| AI suggestion authorization | controller referenced an unregistered model policy | registered action key is `ai.suggestions.resolve` | PASS: administrator browser GET returned 200 and empty state rendered |

## Date and time inventory

| Field | UI source | Laravel validation | Wire rule | Status |
|---|---|---|---|---|
| Agent task `due_at` create/update | `datetime-local` | `Y-m-d\\TH:i:sP`, nullable | local wall time plus numeric UTC offset and seconds | PASS: unit contract and real browser POST/201 |
| Branch holiday `date` | date input | date | `YYYY-MM-DD` | FAIL pending browser verification |
| Branch holiday recurring day | month/day input | request-specific string | preserve request format | FAIL pending browser verification |
| Report schedule `run_at_time` | time input | `H:i` | local clock `HH:mm` plus separate IANA timezone | FAIL pending browser verification |
| Report filters/ranges | date inputs | endpoint-specific | date-only values, no UTC conversion | FAIL pending browser verification |
| Server timestamps | response-only | ISO-8601 resources | parse for display only; never write back implicitly | PASS by source review |

## Cancellation finding

In Vite development, React StrictMode mounts, unmounts, and mounts again. Generated TanStack Query functions pass `signal` to Axios, so the first mount's GETs end as `net::ERR_ABORTED` and replacement GETs return 200. Production does not perform the StrictMode remount. This is expected and should not be disabled. The auth provider used direct Axios calls without cancellation/deduplication, producing duplicate `/auth/me` requests; it now shares one in-flight bootstrap promise and ignores completion after cleanup.
