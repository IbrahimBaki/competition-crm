# Frontend V2 Architecture Decision

## Decision

Adopt a **hybrid frontend rebuild**: retain the existing contract, transport, session, permission, query, domain-adapter, and business-behaviour layers; replace the presentation architecture progressively behind the current URLs and router.

This is the right decision for this repository because the valuable engineering already exists in `src/api/`, `src/auth/`, `src/portal/auth/`, and feature adapters such as `src/features/tickets/api/wire.ts`, `src/features/customers/api/wire.ts`, `src/features/workspace/api/wire.ts`, and `src/features/reports/api/wire.ts`. `orval.config.ts` generates typed React Query clients from `../docs/api/openapi.yaml`; `src/api/http/mutator.ts` handles envelope unwrapping, CSRF retry, locale headers, staff-session recovery, normalized errors, and portal bearer isolation. Recreating that infrastructure would create contract and security regression risk without increasing design freedom.

Conversely, the presentation layer is not a system that can become a premium product through a cosmetic pass. `src/index.css` mixes global shell/component CSS with Tailwind; 123+ TSX files use direct classes; `src/components/ui/index.tsx` is one compact but oversized primitive barrel; staff and portal shells are separately styled (`src/shell/*`, `src/portal/shell/PortalLayout.tsx`); and tables/forms have multiple incompatible implementations. The audit also identifies concentrated mixed-responsibility pages such as `src/pages/operations/OperationsPages.tsx`, `src/pages/operations/CreationPages.tsx`, and `src/pages/admin/ChannelsPage.tsx`.

Why not the alternatives:

| Option | Decision | Repository-specific reason |
|---|---|---|
| Cosmetic redesign | Reject | It leaves raw utilities, duplicate tables/forms, incomplete i18n, and competing shells intact. |
| Styling-only incremental refactor | Reject | It makes the long mixed-CSS period permanent and cannot establish reliable component contracts. |
| Complete rewrite | Reject | It needlessly duplicates OpenAPI, Axios/CSRF/session behavior, query defaults, permissions, adapters, and tests. |
| Separate application/repository | Reject | It duplicates auth/router/API integration and makes parity, rollback, and eventual retirement harder. |
| Hybrid rebuild | Approve | It maximizes presentation freedom while keeping backend and business risk bounded route by route. |

For this repository, “hybrid” means: V2 pages use the same `router.tsx` URL contracts, `AuthProvider`, `PortalAuthProvider`, permission keys, generated APIs, adapters, query cache, error model, translations, and attachment behaviour. They use a new token/component/shell/layout layer and feature views. V1 presentation is retired only after each route meets the migration contract in this document.

`design-system/MASTER.md` is the current product design contract and must be read before future UI implementation. Its current guidance (Plus Jakarta Sans, light enterprise UI, blue trust color and orange CTA, SVG icons, visible focus, 150–300 ms transitions, reduced motion, 375/768/1024/1440 verification) is a migration input, not an excuse to hardcode visual values in feature pages. Page-specific `design-system/pages/*.md`, when present, overrides it.

## Current → V2 Boundary

```text
Laravel backend
    │  ../docs/api/openapi.yaml
    ▼
Orval generated clients and models ─────── src/api/generated/**
    ▼
Transport / normalized API semantics ───── src/api/http/**, src/api/queryClient.ts
    ▼
Domain adapters and query/mutation hooks ─ src/features/*/api, use*.ts
    ▼
──────────────── PRESERVED BOUNDARY ────────────────
    ▼
V2 route composition and feature view models
    ▼
V2 design system, application/portal shells, pages, responsive and RTL UI
```

| Classification | Scope |
|---|---|
| Preserve | OpenAPI, `orval.config.ts`, `src/api/generated/**`, `src/api/http/**`, `src/api/queryClient.ts`, staff and portal auth semantics, permission constants, guards, server-state hooks, adapters, query keys/invalidation knowledge, attachments, route URLs, translations, useful tests. |
| Preserve after cleanup | `src/shell/navigation.ts`, `src/shell/useVisibleNavigation.ts`, `src/shell/AsyncBoundary.tsx`, `src/shell/states/*`, `src/shared/attachments/*`, `features/operations/*`, report descriptors, saved-view serialization. |
| Extract before reuse | Direct requests and payload transforms from `OperationsPages.tsx`, `CreationPages.tsx`, `ChannelsPage.tsx`, `PublicChannelPages.tsx`, portal detail pages, `CollectionPage.tsx`, and `ResourceFormDialog.tsx`. |
| Rewrite | `src/index.css`, `src/components/ui/index.tsx` presentation, `src/shell/{AppLayout,Sidebar,TopBar}.tsx`, `src/portal/shell/PortalLayout.tsx`, tables, form rendering, dashboard rendering, dialogs/dropdowns, raw icons, page composition. |
| Retire only after migration | V1 page and visual components, old global component CSS, `src/pages/DashboardPage.tsx` (unrouted legacy placeholder), and compatibility-only wrappers. Generated files are never manually deleted. |

## Exact reuse matrix

| Current path | Current responsibility | V2 action | Destination | Reason |
|---|---|---|---|---|
| `src/api/generated/**` | Orval endpoint clients/models | KEEP | `src/api/generated/**` | Contract-generated; never hand-edit. |
| `orval.config.ts` | OpenAPI generation configuration | KEEP + HARDEN | root | Retain tags/mutator; generation parity test before contract changes. |
| `src/api/http/{client,csrf,envelope,errors,mutator}.ts` | Axios, CSRF, envelopes, errors, auth recovery | KEEP + HARDEN | `src/api/http/**` | Strong integration boundary; correct blocker only. |
| `src/api/queryClient.ts` | Query defaults/retry policy | KEEP | `src/api/queryClient.ts` | Server-state policy remains product-wide. |
| `src/api/pagination.ts` | Pagination types/helpers | KEEP | `src/api/pagination.ts` | Cross-feature contract. |
| `src/auth/**` | Staff session, guards, permissions | KEEP + HARDEN | `src/auth/**` | Preserve cookie session/2FA/route restoration; fix expiry signal. |
| `src/portal/auth/**` | Portal bearer session/route guard | KEEP + HARDEN | `src/portal/auth/**` | Preserve backend model; add security mitigations, no storage redesign without backend decision. |
| `src/auth/permissions.ts` | Permission constants/scope arrays | KEEP | `src/auth/permissions.ts` | Single typed permission vocabulary. |
| `src/auth/{ProtectedRoute,RequirePermission}.tsx`, `src/shell/ActionGuard.tsx` | Route/action gates | MOVE | `src/auth/guards/**` | Keep behaviour; separate from presentation. |
| `src/features/*/api/wire.ts` | Domain transforms and handwritten endpoints | KEEP + HARDEN | `src/features/<domain>/api/**` | Preserve business mapping; split large admin wire by domain. |
| `src/features/*/use*.ts` | Query/mutation/view helper hooks | KEEP + HARDEN | `src/features/<domain>/model/**` | Retain query behaviour; make keys and invalidation explicit. |
| `src/features/tickets/utils/**`, customer types, report descriptors | Domain formatting/rules | KEEP | corresponding feature `model/` | Useful business knowledge independent of UI. |
| `src/i18n/**` | i18next, locale/direction, EN/AR resources | MOVE | `src/i18n/**` | Preserve content as migration source; remove primitive fallback maps. |
| `src/shared/attachments/**` | Upload/progress/link behaviour | EXTRACT | `src/shared/attachments/{api,model,ui}` | Preserve upload mechanics, replace control presentation. |
| `src/shared/confirm/**` | Destructive-action workflow | REFACTOR | `src/design-system/patterns/confirm-action` | Behaviour is reusable; overlay UI is replaced. |
| `src/shell/navigation.ts`, `useVisibleNavigation.ts` | Navigation metadata/filtering | EXTRACT | `src/routes/navigation.ts` | Make metadata canonical for route/nav/breadcrumb use. |
| `src/shell/AsyncBoundary.tsx`, `shell/states/**` | async/empty/error/forbidden states | REFACTOR | `src/shared/async/**`, `src/design-system/patterns/states/**` | Preserve state semantics, rebuild visual contracts. |
| `src/components/ui/index.tsx` | Button/form/dialog/card primitives | REFACTOR | `src/design-system/{primitives,composites}/**` | Split API and presentation; remove Arabic maps. |
| `src/index.css` | Global reset/tokens, V1 shell/components | RETIRE AFTER MIGRATION | `src/styles/{reset,legacy-v1}.css` then V2 token CSS | It currently contaminates all UI. |
| `src/shell/**` | Staff shell/presentation | REWRITE | `src/app/shell/**` | Reuse nav/auth only, not UI. |
| `src/portal/shell/**` | Portal shell/presentation | REWRITE | `src/portal/shell/**` | One branded system, portal-specific composition. |
| feature list tables/forms/panels | View presentation | REWRITE | `src/features/<domain>/ui/**` | Retain adapters/hooks, replace UI. |
| `src/features/operations/{CollectionPage,ResourceFormDialog}.tsx` | Generic CRUD behaviour plus UI | EXTRACT | `src/features/operations/model/**`; later V2 patterns | Avoid broad untyped generic UI. |
| `src/router.tsx` | URLs, guards, composition | REFACTOR | `src/routes/**` | Preserve URLs/guards; make metadata declarative progressively. |
| `src/__tests__/**`, `src/features/**/__tests__/**`, `e2e/**` | business and route coverage | KEEP + EXTEND | same locations / `tests/v2/**` | Existing tests encode parity constraints. |

# Proposed V2 Source Architecture

```text
src/
├── app/
│   ├── providers/                 # Query, auth, locale, toast composition
│   ├── shell/                     # Staff shell only: composition, no domain API
│   ├── bootstrap.tsx
│   └── App.tsx
├── api/
│   ├── generated/                 # Orval output only
│   ├── http/                      # Axios, CSRF, errors, envelope, mutator
│   ├── queryClient.ts
│   └── pagination.ts
├── auth/
│   ├── guards/                    # ProtectedRoute, RequirePermission, ActionGuard
│   ├── session.ts
│   ├── AuthProvider.tsx
│   └── permissions.ts
├── design-system/
│   ├── foundations/               # token CSS/TS, typography, motion, icons
│   ├── primitives/                # Button, Input, Dialog foundations etc.
│   ├── composites/                # FormField, Menu, DataTable parts
│   ├── patterns/                  # async states, page header, filter bar
│   └── __tests__/
├── features/
│   ├── tickets/{api,model,ui,routes,__tests__}/
│   ├── customers/{api,model,ui,routes,__tests__}/
│   ├── workspace/{api,model,ui,routes}/
│   ├── knowledge/{api,model,ui,routes}/
│   ├── reports/{api,model,ui,routes}/
│   ├── admin/{organisation,security,catalogue,sla,automation,channels,api,model}/
│   └── operations/{api,model}/    # temporary extraction boundary, not generic V2 UI
├── i18n/                          # config, locale provider, locales/en.json/ar.json
├── portal/
│   ├── auth/
│   ├── shell/
│   ├── features/{tickets,help,account,guest}/
│   └── routes/
├── routes/
│   ├── config/                    # typed URL, title, guard, nav/breadcrumb metadata
│   ├── staff.tsx
│   ├── portal.tsx
│   ├── public.tsx
│   └── router.tsx
├── shared/
│   ├── async/                     # boundary/view-model utilities
│   ├── attachments/{api,model,ui}/
│   ├── hooks/
│   ├── lib/                       # date, bilingual, URL-state helpers; no feature rules
│   ├── types/
│   └── test/
├── styles/
│   ├── reset.css
│   ├── tokens.css
│   ├── globals.css
│   └── legacy-v1.css              # temporary, explicitly scoped/removed at retirement
└── main.tsx
```

Top-level contracts:

| Folder | Responsibility | May import | Must not contain / import |
|---|---|---|---|
| `app` | provider and shell composition | routes, auth, design system, shared | endpoint strings, feature business transforms |
| `api` | generated/transport/server-cache infrastructure | auth session only where recovery requires it | feature UI, design-system imports |
| `auth` | staff identity and permission decisions | api/http, shared types | feature UI or navigation styling |
| `design-system` | tokens and reusable visual/a11y contracts | React, i18n for component labels only, low-level utilities | feature modules, endpoint calls, permission logic |
| `features` | domain-specific adapters, model/hooks, UI | api, auth, design-system, shared, i18n | another feature’s internals; generic global styles |
| `portal` | portal auth and portal-specific features/shell | api, design-system, shared, i18n | staff feature UI and staff auth |
| `routes` | route declarations and metadata | app shell, auth guards, feature route exports | endpoint calls and visual primitives |
| `shared` | domain-neutral helpers/pattern behaviour | api/design-system where layered | ticket/customer/admin rules |
| `styles` | reset/tokens/global document rules only | none | feature/shell selectors, component variants |

## Dependency direction rules

```text
OpenAPI → generated API → transport → domain adapter/service → feature model/query/mutation
       → feature UI → route composition → app/portal shell
design-system ← feature UI / app / portal (never points right)
```

1. Generated functions/models are consumed only by `api` infrastructure or a feature `api/` adapter; pages never call them directly.
2. A feature UI component receives typed data and callbacks from its model/container. It does not call `apiRequest`, Axios, or endpoint literals.
3. Endpoint strings, request payload transforms, idempotency keys, and cache invalidation live in feature `api/`/`model/`, not JSX.
4. Only `src/auth/permissions.ts` defines permission literals. Route metadata and `ActionGuard` use those constants.
5. A design-system primitive has no business permission, route, API, or feature dependency. A pattern may accept state/callbacks but cannot decide ticket/customer policy.
6. Feature A imports Feature B only through an explicitly exported, UI-free contract in `features/<b>/model`; prefer shared extraction over feature-to-feature imports. No cycles.
7. Backend authorization is authoritative. Client guards govern navigation, discoverability, and safe interaction only.
8. Route composition may choose a feature screen; it may not implement domain data transformations.

## API Architecture V2

| Layer | Responsibility | Current evidence / V2 rule |
|---|---|---|
| Generated | Endpoint operation, request/response types, generated Query hooks | Keep `src/api/generated/**` generated by `orval.config.ts`; never patch. |
| Transport | Base URL, cookies/CSRF, portal bearer attachment, locale, retries, envelopes/errors | Keep `src/api/http/{client,csrf,envelope,errors,mutator}.ts`; feature code never imports Axios. |
| Domain adapter/service | Convert generated/wire shapes into named domain operations; isolate non-generated endpoints | Keep/split `features/*/api/wire.ts`; one domain operation per purpose. |
| Feature query/mutation | Query keys, enabled conditions, URL parameter translation, mutation invalidation, optimistic rules | Locate in `features/<domain>/model/queries.ts`, `mutations.ts`, `queryKeys.ts`. |
| UI consumption | Render typed view state and call callbacks | `features/<domain>/ui/**`; no API transport imports. |

Real ticket target flow:

```text
../docs/api/openapi.yaml
 → `src/api/generated/ticketing/ticketing.ts` + `model/**`
 → `src/features/tickets/api/wire.ts` (split to `api/tickets.ts`, `api/queues.ts`)
 → `src/features/tickets/model/useTicketListQuery.ts`, `useTicketMutation.ts`
 → `src/features/tickets/ui/list/TicketListScreen.tsx`
 → `src/routes/staff.tsx` at `/tickets`
```

`TicketComposer.tsx` should retain message/attachment domain integration, but V2 separates its mutation/controller from its composer visual surface. The same rule applies to `CustomerIdentityPanel.tsx`, `RoleEditor.tsx`, and organization forms.

## Direct API call cleanup plan

Direct calls still use the safe mutator, but bypass the desired typed/domain boundary. Categorize before moving—not every call must be generated if OpenAPI lacks it.

| File | Direct endpoint/API usage | Risk | Recommended V2 destination |
|---|---|---|---|
| `src/pages/operations/OperationsPages.tsx` | AI suggestions/usage, retention, 2FA actions and others | High: mixed pages, literals, UI/payload coupling | split into feature `api/ai.ts`, `api/dataProtection.ts`, `auth/security.ts`, then model hooks |
| `src/pages/operations/CreationPages.tsx` | ticket/customer/knowledge creation and edit calls | High: core mutations in page JSX | `features/tickets|customers|knowledge/{api,model}` |
| `src/pages/admin/ChannelsPage.tsx` | channel config/action/transcript endpoints | High: dynamic endpoint construction in UI | `features/admin/channels/{api,model}` |
| `src/pages/admin/SlaPoliciesPage.tsx` | SLA CRUD | Medium | `features/admin/sla/{api,model}` |
| `src/pages/admin/UsersPage.tsx` | user action | Medium | `features/admin/security/api/users.ts` |
| `src/pages/auth/RecoveryPages.tsx` | recovery/invitation actions | Medium | `auth/api/recovery.ts` |
| `src/features/knowledge/KnowledgeLifecyclePanel.tsx` | lifecycle calls | Medium | `features/knowledge/api/lifecycle.ts` |
| `src/features/operations/{CollectionPage,ResourceFormDialog}.tsx` | parameterized CRUD endpoints | High: generic strings spread to callers | first extract domain adapters; do not reproduce generic string-driven V2 UI |
| `src/features/tickets/detail/TicketAiPanel.tsx` | AI ticket action | Medium | `features/tickets/api/ai.ts` |
| `src/pages/portal/{PortalHelpArticlePage,PortalRegisterPage,PortalVerifyPage,PortalTicketDetailPage}.tsx` | portal/public endpoint calls/downloads | Medium | `portal/features/<domain>/{api,model}` |
| `src/portal/auth/PortalAuthProvider.tsx` | portal login/me/logout | Accepted auth boundary | retain in `portal/auth`; use generated endpoint when available |
| `src/auth/{AuthProvider,session}.ts` | staff auth/me/session calls | Accepted auth boundary | retain in `auth`; do not route through feature adapters |

Migration rule: no new V2 JSX may contain `apiRequest(`, `httpClient.`, a relative API endpoint literal, or cache invalidation strings. ESLint/architecture tests should enforce it outside `api/**`, `auth/**`, and named feature `api/**` modules. If the OpenAPI operation is absent, add exactly one named adapter with a documented contract gap; do not add a UI-local request.

## Authentication Architecture V2

Staff authentication stays cookie/Sanctum based. `AuthProvider` bootstraps CSRF (`ensureCsrfCookie`) then `/auth/me`; `login` posts `/auth/login`, handles the 2FA-required state, then reloads the current user; `completeTwoFactor` posts `/auth/two-factor/challenge`; logout posts `/auth/logout`, clears session/query cache, and navigates to `/login`. User data remains in memory through `src/auth/session.ts`, not browser token storage. `ProtectedRoute` retains requested-route restoration and redirects pending 2FA to `/login/two-factor`.

The V2 login/recovery UI must call these same auth operations and preserve normalized error/field behavior. Authentication loading becomes a design-system state, not a new auth implementation. Auth bootstrap and protected route tests must execute before V2 shell migration.

Portal authentication stays separate: `PortalAuthProvider` uses `/portal/auth/login`, stores the returned bearer session with `portalSession.ts`, retrieves `/portal/me`, attaches the token only for `/portal/*` in `mutator.ts`, and posts bearer logout. `PortalProtectedRoute` remains the portal route decision point. Do not attach portal tokens to staff calls.

## V2 BLOCKER-001 — session expiry signalling

**Confirmed.** `src/api/http/mutator.ts` dispatches `window.dispatchEvent(new Event('session:expired'))` after unrecoverable staff 401 recovery. `src/auth/AuthProvider.tsx` subscribes with `on('session:expired', ...)` from `src/auth/session.ts`. That module’s in-memory listener set is only notified by `emit(...)`; no bridge or browser-event listener exists in the inspected source.

Likely failure: a request rejects with a normalized unauthenticated error after the session has expired, while `AuthProvider` may retain authenticated UI/session state instead of clearing Query and redirecting to `/login?reason=session_expired`. The focus revalidation path also calls `httpClient` directly, so it cannot rely on the mutator for redirect behavior.

Safest correction: choose **one** session-event authority—prefer the existing typed in-memory `emit('session:expired')` in `mutator.ts`, imported from `auth/session.ts`; alternatively have `AuthProvider` listen to the browser event. Do not keep both without a deliberate bridge/deduplication rule. The correction belongs in Phase 0 before V2 authenticated UI work.

Required tests: failed recovery emits one signal; provider clears user/session/query cache; redirect retains the reason; concurrent 401s produce one recovery/redirect; 419 retry does not expire session; portal 401 never triggers staff expiry; focus/visibility revalidation expires cleanly. This is the first actual UI-work blocker.

## Portal authentication security

`src/portal/auth/portalSession.ts` persists `__portal_session__` (bearer token and optional expiry) and `__portal_user__` in `localStorage`. `PortalAuthProvider.tsx` relies on persistence for reload restoration; `mutator.ts` adds it only to portal URLs. This is an apparent backend/SPA bearer-token assumption, not a V2 visual problem.

An XSS payload can read localStorage and exfiltrate the bearer token. Changing to HttpOnly cookie storage requires backend/session/CORS/Sanctum design discussion; it cannot be safely assumed as a frontend-only swap. Frontend-only mitigations: retain strict portal/staff header separation, avoid unsafe HTML rendering, maintain dependency/XSS hygiene, use CSP/nonces where hosting permits, never log tokens, clear malformed/expired sessions, and limit exposure in UI. Classification: **important; requires backend discussion; not a V2 blocker**. It is not an accepted risk merely because it exists—record the decision in an ADR.

## Permissions & authorization architecture

| Layer | V2 responsibility |
|---|---|
| Route permission | Typed route metadata references `PERMISSIONS`/scope arrays and is enforced by `ProtectedRoute`/`RequirePermission`. Prevents direct URL access from rendering a protected screen. |
| Action permission | `ActionGuard` or an equivalent wraps create/edit/delete/transition/export controls. The mutation must still handle server 403. |
| Navigation visibility | Derive from route/navigation metadata through `useVisibleNavigation`; it prevents misleading menu entries but is never an authorization decision. |
| Component visibility | Use a named `Can`/`ActionGuard` only where a screen has independently authorized controls/panels. Avoid string checks or hiding required explanatory content without intent. |
| Backend authorization | Laravel remains authoritative for every request, including one reached through a stale client or forged UI. |

Make `src/routes/config/**` the centralized metadata source for staff routes: id, path, nav label/icon, breadcrumb key, and route permission. Keep feature-level action permissions next to the action because a route cannot describe all controls. Derive `NAVIGATION` from this metadata rather than duplicate it, after a parity test proves the visible tree is unchanged.

## Routing Architecture

**Preferred strategy: B — restructure router configuration while preserving every current URL, by route-by-route component replacement.** Do not create `/v2` URLs or a separate entry point. The existing `createBrowserRouter` remains; `src/router.tsx` is split internally into `src/routes/{staff,portal,public}.tsx` and typed metadata over time. A route initially points at the V1 component, then swaps to the V2 component only when parity passes. This preserves deep links, bookmarks, guard semantics, browser history, auth reuse, and rollback.

| Existing route group | V2 destination | Migration strategy | Permission preserved? |
|---|---|---|---|
| `/login`, `/login/two-factor`, recovery/invitation | `auth/ui/**` | replace presentation on same public routes | Yes; auth state/2FA stays. |
| `/`, `/workspace` | `features/workspace/routes` | same route, V2 screen after shell | authenticated parent guard. |
| `/tickets`, `/tickets/new`, `/tickets/:ticketId` | `features/tickets/routes` | POC then complete ticket set | `TICKETS_VIEW_SCOPES`, create/action guards. |
| `/customers`, new/detail | `features/customers/routes` | after ticket patterns stabilize | customers route/action checks. |
| `/knowledge/**` | `features/knowledge/routes` | migrate list/detail/form lifecycle as a group | existing article permissions. |
| `/reports/**`, `/dashboard`, schedules | `features/reports/routes` | preserve report IDs/filter contracts | report/schedule scopes. |
| `/admin/**` | `features/admin/**/routes` | domain-by-domain, never huge Operations page copy | current per-route/per-action permissions. |
| `/portal/**` | `portal/routes` | portal shell then feature groups | portal guard only for existing protected paths. |
| `/forms/**`, `/chat` | `portal/features/public` or `features/public` | migrate after portal/public primitives | public remains public. |
| `*` | shared route state | retain `NotFoundState` semantics | N/A. |

## Migration isolation strategy

Recommend **Option B: introduce V2 design-system and feature layers inside the existing application, using route-by-route component replacement**, with temporary V1 CSS explicitly quarantined. This is not a permanent `src/v2/**` application.

| Option | Assessment |
|---|---|
| A: `src/v2/**` | Good short-term visual boundary but tends to become a permanent duplicate architecture and invites imports across the boundary. |
| B: new foundation/features in current architecture | **Recommended.** One app, auth, router, Query cache and API; migration boundaries are feature routes and V1 retirement commits. |
| C: separate entry point | Duplicates provider/auth/router bootstrap and complicates deep links. |
| D: separate repository | Highest drift and integration/rollback cost. |
| E: unstructured route component replacement | Necessary mechanism but insufficient without new layers/tokens and CSS quarantine. |

V1 and V2 are protected by import rules: V2 may import preserved API/auth/model modules but never V1 page/shell/UI components; V1 cannot import V2 primitives except through explicitly approved compatibility wrappers; no V2 feature adds selectors to V1 global CSS. Each replacement is reversible by changing one route element. Delete V1 only after the parity checklist passes, then remove its compatibility CSS in the same tracked retirement phase.

## CSS & styling isolation

`src/index.css` currently contains reset, tokens, global selectors, V1 shell, primitive, dialog, table, responsive, RTL, and reduced-motion CSS. `tailwind.config.js` has an empty extension; therefore raw Tailwind palette/radius/shadow classes bypass the semantic variables. The V2 migration must not append more broad selectors to this file.

Foundation approach:

1. Keep `index.css` unchanged until V2 foundations exist; then split it deliberately into a minimal document reset/global layer and a temporary `legacy-v1.css` loaded only while V1 remains.
2. V2 token CSS defines semantic variables (including z-index, motion, breakpoint and logical spacing tokens) at `:root`; component styling uses token-backed classes or CSS modules/recipe classes, not raw palette utilities.
3. Tailwind, if retained, is a layout utility tool: configure semantic token utilities only after approval. It is not the V2 component API.
4. Prefix/scoping is a fallback, not the primary architecture. New V2 components must have encapsulated class namespaces/recipes and never target `.ui-*`, `.app-*`, `.collection-*`, or tag selectors from V1.
5. Audit global `:has`, `html[dir]`, breakpoint and z-index rules before moving them. Preserve reset/focus/reduced-motion semantics while replacing V1 selector behaviour.

## Design-system architecture — structure only

```text
src/design-system/
├── foundations/{tokens.css,tokens.ts,typography.css,motion.ts,breakpoints.ts,zIndex.ts,icons.ts}
├── primitives/{Button,IconButton,Input,Textarea,Select,Checkbox,Radio,Switch,Label,Badge,Avatar}/
├── composites/{FormField,Dialog,Drawer,Popover,Menu,Tooltip,Tabs,DataTable,Pagination}/
├── patterns/{PageHeader,FilterBar,FormSection,FormActions,AsyncState,EmptyState,ErrorState,ConfirmAction}/
└── __tests__/
```

Foundations define color, typography, spacing, radius, elevation, motion, breakpoints, z-index and direction tokens. Primitives supply accessible single-purpose controls. Composites coordinate primitives (for example dialog focus behaviour or table structure) but know no CRM domain. Patterns standardize repeated product workflows (filter bar, form section, async state). Feature components compose patterns and domain data; they never add a second Button/Table/FormField system.

### Styling rule for new V2 features

Token/design-system components are mandatory for color, typography scale, spacing scale, radius, elevation, motion, controls, overlays, feedback, form fields, tables, and navigation. Raw utility classes are acceptable for local structural layout (`grid`, `flex`, `min-w-0`, responsive placement), one-off semantic HTML flow, and explicitly token-backed utilities. They are not acceptable for arbitrary hex/slate colors, `rounded-*`, `shadow-*`, bespoke button/input/table styling, undocumented z-index values, or unreviewed animation. A feature needing a new reusable visual treatment adds a primitive/composite/pattern with tests rather than copying classes.

## Required V2 component inventory

| Category | Required inventory |
|---|---|
| Foundation primitives | Button, IconButton, Input, Textarea, Select/Combobox, Checkbox, Radio, Switch, Label, Badge, Avatar, Link, Spinner, VisuallyHidden. |
| Overlay primitives | Dialog, alert dialog, drawer, popover, dropdown/menu, tooltip, command/search surface. |
| Navigation | staff sidebar, nav item/group, top bar, breadcrumb, tabs, pagination, mobile navigation drawer, portal header/footer. |
| Feedback | toast, alert, progress, skeleton, inline field error, EmptyState, ErrorState, ForbiddenState, LoadingState, NotFoundState. |
| Data display | DataTable, responsive DataList/card view, metric, card, description list, status badge, timeline, activity/message thread, attachment row. |
| Forms | FormField, FormSection, FormActions, error summary, async combobox, file upload field, server-error mapper. |
| CRM product patterns | ticket queue/saved view/filter bar/bulk toolbar; ticket workbench/conversation/composer/SLA/status/assignment; customer identity/contact/timeline/merge; workspace queue panels/tasks/quick replies; report filter/date-range/metric/export; admin resource list/editor; knowledge lifecycle; portal ticket/help/guest tracking; public web form/chat. |

## UI library decision matrix

`PROPOSED — requires approval before installation`: use **Radix UI primitives selectively, with custom V2 branded wrappers**. It gives focus, dismissible layers, keyboard patterns and composability needed by dialogs, menus, popovers, tabs and tooltips; V2 owns all styling and CRM-specific table/form patterns.

| Option | Accessibility/RTL | Brand freedom | Enterprise data UI | Migration/bundle | Decision |
|---|---|---|---|---|---|
| Existing handcrafted primitives | Mixed (Dialog good; menus weak) | High | Insufficient coverage | Lowest immediate, high maintenance | Reuse behaviour selectively only. |
| Radix UI | Strong primitives; direction support must be configured/tested | High | Pair with custom table/form | Small selective additions | **Proposed.** |
| shadcn/ui | Good starting recipes but is copied code, not a dependency strategy | High | Still needs table/form architecture | Can duplicate V1-style drift | Do not adopt as the architecture; may use individual patterns only after review. |
| React Aria | Excellent accessibility | High | More implementation work | Moderate | Viable alternative if team prefers hooks; not preferred. |
| Headless UI | Adequate | High | Less broad enterprise primitives | Moderate | No advantage over Radix here. |
| AG Grid | Strong grid capabilities | Limited visual ownership/large | Overpowered for current evidence | High bundle/migration | Reject for POC. |

Icon decision: `PROPOSED — requires approval before installation`: Lucide React (or another single SVG icon library) is preferred over repeated inline SVG/text glyphs; confirm RTL mirroring policy before adoption.

## Form architecture decision

`PROPOSED — requires approval`: adopt **React Hook Form + Zod**, retaining backend validation as authoritative and mapping normalized server field errors into the form error model. Zod is already installed; React Hook Form is the only proposed addition. It suits complex ticket/customer forms, dynamic admin fields, Arabic/English fields, async options, uploads, nested payloads and fewer controlled rerenders. Native controls remain semantic and design-system `FormField` owns label/help/error association.

Do not migrate every V1 form first. Create adapters for `normaliseApiError`, bilingual fields and attachment state, prove them in ticket creation/detail and customer creation, then migrate. Alternative—continuing page-local controlled state—has no package cost but perpetuates duplicated busy/error/transformation logic. Formik is not preferred for this performance/architecture case.

## Data table architecture decision

Requirements observed in `TicketListTable.tsx`, `CustomerListTable.tsx`, organization tables, `CollectionPage.tsx`, reports, portal lists: server pagination/filter/search/sort; ticket selection and bulk actions; permission-aware row actions; async/empty/error; accessible semantics; responsive list/card alternative; report export; and direction-aware display. Virtualization is not currently required by evidence.

`PROPOSED — requires approval`: use a custom V2 `DataTable` composition initially, with a typed column model and semantic HTML table on desktop plus an explicit `DataList` mobile representation. It consumes server data and URL state but owns no fetching. Evaluate **TanStack Table** only when column visibility/reordering, complex sorting/selection, or cross-feature table mechanics justify it; it is the preferred library option over AG Grid. Native handcrafted tables without a shared contract repeat current drift; AG Grid is excessive in bundle, branding and migration cost for the POC.

## URL state architecture

Use typed `URLSearchParams` codecs in `shared/lib/urlState` and feature schemas. URL-backed state is shareable, reload-safe and query-key input; changing it resets page where appropriate.

| Feature | URL-backed | Local only |
|---|---|---|
| Tickets | `page`, `perPage`, `q`, queue mode, status/priority/assignee/department/category filters, sort/direction, saved-view id where representable | selected row IDs, open bulk dialog, composer draft (persist explicitly only if approved). |
| Customers | page, q, status/source/owner filters, sort/direction | open contact/note editor, merge selection. |
| Workspace | active queue/tab and explicit date/filter choices | panel expansion, transient task dialog. |
| Reports/dashboard | report id route, date range, mandatory report filters, sort/page, visible tab | chart hover, expanded metric/card. |
| Knowledge | q, category/state, page, sort | lifecycle dialog/editor draft. |
| Admin collections | page, q, filter/sort, active tab | edit/create dialog state unless it has a routable deep-link contract. |
| Portal tickets/help | page, q/status/filter and help search/category where server supported | feedback/modal/composer transient state. |

Saved views may remain localStorage only behind a named adapter and versioned schema; do not mix arbitrary localStorage reads into components.

## Localization and RTL architecture

Keep i18next/react-i18next and move `src/i18n/en.json`, `src/i18n/ar.json`, and locale/direction provider intact as the migration source. New visible text, labels, validation text, empty/error strings, and ARIA labels use semantic keys—not hardcoded English, and never hardcoded Arabic fallback maps inside primitives. Existing `components/ui/index.tsx` Arabic field/title mappings are retired with that V1 primitive implementation.

Use feature namespaces (for example `tickets.list.empty.title`, `portal.ticket.reply.send`) and shared namespaces (`common.actions.save`, `a11y.close_dialog`). Translate status/value labels through explicit domain maps keyed by backend values; do not translate raw server values ad hoc. API `Accept-Language` behaviour remains in the mutator.

RTL rules: use logical CSS properties; use `dir` context rather than manual left/right classes; mirror only directional icons (chevrons, arrows, send/back), not semantic icons (status, user, attachment); reverse sidebar placement and drawer origin through logical positioning; verify table alignment/scroll affordances, form labels, charts axis/legend/tooltips, breadcrumbs, keyboard arrow semantics, and directional enter/exit animations. Test Arabic content expansion at every supported viewport.

## Accessibility architecture

Preserve and make mandatory the good existing behavior: skip links, `:focus-visible`, semantic labels/errors, `FormErrorSummary` focus, dialog focus loop/Escape/restore, toast live region, semantic tables, loading announcements, and `prefers-reduced-motion`. Correct known weak areas while replacing them: user menu keyboard/outside-dismiss semantics in `TopBar.tsx`, unlabeled portal controls, attachment input labeling in `AttachmentUploader.tsx`, small targets, and hardcoded ARIA text.

Release gates for every V2 route: keyboard-only task completion; named landmarks/headings; focus order/return; no keyboard traps except intentional modal containment; translated accessible names; field error association; contrast; semantic desktop table or usable mobile list; reduced motion; axe automated checks; Playwright desktop/tablet/mobile and Arabic RTL. Add component tests with Testing Library/user-event, axe integration tests, and browser workflow tests. Screen-reader spot checks (NVDA/VoiceOver) are required for shell, dialog, table, form and portal critical flows.

## Motion architecture

Motion is purposeful, tokenized, and non-blocking: micro (button/field/menu acknowledgement), component (dialog/drawer/dropdown/tabs), layout (sidebar/content), page (very limited route view change), and data (skeleton/state transition). Use 150–300 ms tokens, compositor-friendly opacity/transform, no layout-shifting hover scales, no motion that delays clicks/navigation, and static/final states under reduced motion. Data refresh must never animate away user context.

`PROPOSED — requires approval`: add Motion/Framer Motion only if the approved shell/design prototype needs coordinated presence/layout transitions that CSS cannot express cleanly. CSS transitions plus token utilities suffice for the POC otherwise. Do not introduce it merely for decorative animation.

## Branding architecture

Future assets live under `src/brand/`: `config.ts` (product/staff/portal naming and semantic asset references), `assets/logo/*`, `assets/icons/*`, `assets/illustrations/*`, `fonts/*` only if self-hosted, and `favicon/*`. Semantic brand tokens live in `design-system/foundations`; public `index.html` favicon/app-icon integration is a future approved implementation change. Do not embed logo text/S-mark strings in shell components. Staff and portal use the same brand config, with approved contextual variants rather than independent brands.

### Staff vs portal relationship

Use **one design system with staff and portal themes/compositions**, not separate component systems. Foundations, primitives, feedback, typography, icon and accessibility rules are shared. Staff owns dense operational patterns; portal owns calmer self-service layouts and public/guest patterns. Theme/context variants may differ only through semantic tokens and approved layout patterns, never forked Button/Input/Dialog implementations.

## Dashboard architecture

Retain report/query logic from `features/reports/api/wire.ts`, `features/reports/filters/{descriptors,useReportFilters}.ts`, report pages, and generated reporting operations. Build a V2 report workspace with typed report filter schema, URL-backed date/filter state, query controller, metric/card/table/chart presentation slots, export state, permission gate, and async/error/empty states. `/dashboard` stays report-driven (`ManagementDashboardPage` is the active route); do not migrate unrouted `pages/DashboardPage.tsx`.

No chart library exists. `PROPOSED — requires approval`: evaluate Recharts for branded responsive charts after data shapes and approved dashboard design exist. Alternative: no chart package in POC, using metric/table states. Never imply an export download/status endpoint where `features/reports/types.ts` and `api/wire.ts` document that it does not exist.

## Feature migration order

| Order | Feature | Why now | Dependencies | Risk | What it validates |
|---:|---|---|---|---|---|
| 0 | V2 blocker hardening | Auth expiry is foundational | API/auth tests | High | controlled expired-session behaviour. |
| 1 | Architecture foundation | Prevents one-off UI | routes metadata, CSS isolation, i18n rules | Medium | boundaries, tokens, test harness. |
| 2 | Design-system core | Every later screen needs it | foundation approval | Medium | a11y/RTL/motion/control contracts. |
| 3 | Login + recovery frame | Lowest protected-screen risk, validates brand/auth | existing `AuthProvider` | Medium | auth, validation, loading/error, LTR/RTL. |
| 4 | Staff shell + navigation | Needed by staff POC | nav metadata, guards, overlay primitives | High | responsive shell, permissions, menu a11y. |
| 5 | Workspace | Uses real queues without dense detail complexity | workspace hooks | Medium | dashboard panels, async states, navigation. |
| 6 | Tickets list | Highest-value table/filter case | table, URL state, ticket adapter | High | server list, bulk actions, permission UI, responsive data patterns. |
| 7 | Ticket detail/new | Completes main workflow | composer, attachments, forms, mutations | High | forms, cache invalidation, rich responsive workbench. |
| 8 | Customers | Reuses list/detail/form patterns | customer adapters | High | contact/timeline/merge and customer permissions. |
| 9 | Knowledge | Clear CRUD/editor/public tie-in | knowledge adapters | Medium | content lifecycle, render/preview. |
| 10 | Reports/dashboard/schedules | Needs settled table/filter/chart decisions | report model | Medium | metrics/export/filter architecture. |
| 11 | Admin organization/security | Many repetitive but sensitive forms | admin adapter split | High | dynamic forms and granular admin gates. |
| 12 | Catalogue/SLA/automation/channels/integrations/AI/data protection/audit | Most direct-call/generic CRUD debt | extracted domain APIs | High | remaining operational patterns. |
| 13 | Portal authenticated and guest/help/public forms/chat | Shares system but distinct UX/security | portal shell/auth | High | portal token isolation, public responsiveness. |
| 14 | V1 retirement | Only after parity evidence | all prior | High | cleanup without business loss. |

## V2 proof of concept scope

Use `/login`, `/workspace`, `/tickets`, and `/tickets/:ticketId` (plus the existing `/tickets/new` route only if form readiness is needed to prove mutation architecture). This is technically optimal: it proves staff cookie auth and requested-route restoration, shell/navigation permissions, real workspace queries, the heaviest list/filter/selection/table case, ticket detail composition, attachment/composer mutation paths, and responsive/RTL states without committing to admin/portal migration.

Required POC states: auth bootstrap/loading/login failure/2FA handoff; workspace loading/empty/error/forbidden/populated; ticket list loading/empty/error/no permission/populated/long data/selection/bulk action; detail loading/not found/error/no permission/message/attachment state; desktop 1440, tablet 768, mobile 375; English LTR and Arabic RTL; keyboard/reduced-motion. It must use live-compatible backend contracts/MSW contract fixtures, never fake replacements where endpoints exist.

## Feature migration contract

Before retiring a V1 route, certify all items:

- [ ] Same public URL, parameters, deep-link and back/forward behavior.
- [ ] Same auth, session-expiry, requested-route and portal/staff isolation behavior.
- [ ] Same route permission, action permissions, navigation visibility and backend 403 handling.
- [ ] Same generated/domain API operation, payload transformation, idempotency expectation, mutation outcome and cache invalidation.
- [ ] Same server/client validation and normalized field/form error presentation.
- [ ] Loading, error, empty, forbidden/unauthorized, not-found and populated states.
- [ ] URL-backed filter/sort/page state where specified, with stable query keys.
- [ ] Desktop/tablet/mobile, English/Arabic, LTR/RTL, long content and keyboard accessibility.
- [ ] Attachments/downloads/export limitations preserved where applicable.
- [ ] Unit/integration/E2E parity tests pass, including route smoke and relevant visual/a11y checks.
- [ ] Product/design approval records the replacement; V1 component/CSS removal is a separate reviewed change.

## Testing and V1/V2 regression protection

Preserve `src/__tests__/{no-hardcoded-strings,no-role-names,no-sla-recalculation,portal-isolation}.test.ts`, feature tests, MSW setup, and `e2e/{route-smoke,core-workflows,responsive}.spec.ts`. Extend Playwright beyond its current desktop-only project to 375/768/1440 and Arabic RTL. Do not rely on checked-in `playwright-report/` or `test-results/` as evidence.

Before replacing tickets/customers, add contract-facing MSW tests for visible fields, query parameters, payloads, status/error mapping, invalidation/refetch and redirects; permission matrix tests for route/nav/action visibility; URL-state serialization tests; and route-level visual snapshots only after design approval. Add axe checks to primitive and critical-route tests. For migration parity, execute V1 and V2 against the same fixtures and assert equivalent requests/results/visibility—not identical DOM or styling.

## Technical blockers before UI work

| Priority | Item | Decision |
|---|---|---|
| MUST FIX BEFORE V2 UI | `V2 BLOCKER-001` session expiry mismatch | Resolve and test before authenticated POC. |
| MUST FIX BEFORE V2 UI | Explicit CSS isolation plan/load order | Define as part of foundation before V2 shell styles. |
| FIX DURING FOUNDATION | Direct UI API calls | Extract incrementally before each affected route; not a global precondition. |
| FIX DURING FOUNDATION | i18n primitive fallback maps and hardcoded new text | Enforce for V2; migrate old strings per route. |
| FIX DURING FOUNDATION | Route/nav metadata duplication | Centralize with parity test before shell switch. |
| CAN WAIT | Portal localStorage bearer replacement | Backend security ADR/discussion; mitigations now, no blind swap. |
| CAN WAIT | Dead `DashboardPage.tsx`, old Vite config artifacts | Retire only in V1 cleanup after verification. |
| CAN WAIT | Major platform upgrades | Keep separate from the visual migration. |

## Dependency change proposal

Retain: React 18, React Router, TanStack Query, Axios, Orval, i18next/react-i18next, Tailwind/PostCSS until the approved styling transition, Zod, Vitest/Testing Library/MSW/Playwright.

Reconsider after V1 retirement: Tailwind only if the approved V2 token/recipe approach no longer needs it; duplicated legacy CSS/handwritten primitives; Zod only if the approved form architecture does not adopt it (current recommendation does adopt it). Never remove generated/API/test dependencies as UI cleanup.

| Proposed addition | Problem solved | Alternative | Migration cost | Required for POC? |
|---|---|---|---|---|
| React Hook Form | complex typed forms, server errors, performance | controlled state | moderate form adapter work | Yes for ticket form proof if `/tickets/new` is in POC; otherwise foundation only. |
| Radix UI (selective) | accessible overlays/menu/tabs | React Aria/custom | wrappers/design work | Yes for polished shell/dialog/menu POC. |
| Lucide React | consistent SVG icon system | curated local SVG assets | low | Yes for shell POC. |
| TanStack Table (conditional) | shared headless table state | custom typed table | moderate | No; decide after ticket list requirements are proven. |
| Framer Motion (conditional) | coordinated motion | CSS transitions | moderate | No. |
| Recharts (conditional) | dashboard charts | no charts/custom SVG | moderate | No. |
| axe test integration | automated a11y regression | manual-only checks | low | Yes. |

All additions are **PROPOSED — requires approval before installation**.

## Upgrade strategy

Do not upgrade React, Vite, TypeScript, Orval, Router, or Query before or during the POC. The current baseline builds and its integration behavior is a migration control. Evaluate upgrades after V2 retirement or in a dedicated, tested infrastructure branch once visual migration has stabilized. A security-critical upstream fix is the sole exception and requires a separate decision/validation plan.

## Git / migration safety strategy

Use a long-lived V2 integration branch only if the team workflow requires it, with short feature branches/PRs per foundation or route group. Commit separately: extracted logic/tests, V2 component foundation, route replacement, and V1 retirement. Keep V1 route elements operational until acceptance; a rollback is reverting the small route-replacement commit. Feature flags are optional and useful only for staged internal validation—never expose parallel permanent URLs. Do not make broad formatting, dependency upgrades, generated-code rewrites, or unrelated cleanup in migration PRs.

## Naming convention and responsibility guidelines

Folders use lowercase kebab-case only where existing convention requires it; new domain folders are lowercase (`tickets`, `data-protection`). Components use PascalCase descriptive names (`TicketFilterBar.tsx`); hooks are `useXxx.ts`; APIs are verb/noun modules (`api/createTicket.ts` or grouped `api/tickets.ts`); query keys are exported from `model/queryKeys.ts`; schemas are `ticketForm.schema.ts`; domain types are `types.ts`/named model files; tests co-locate as `*.test.ts(x)`; test fixtures live in `__tests__/fixtures`; token files use semantic names (`color.tokens.css`, `motion.ts`). Avoid barrel files that silently re-export whole subsystems; use explicit public exports per feature.

Do not use line count as the main rule. A file must have one cohesive responsibility and stable collaborators. `OperationsPages.tsx` should become independently routed domain screens; `CreationPages.tsx` becomes domain-specific form screens/controllers; `ChannelsPage.tsx` becomes channel subfeatures; the UI barrel becomes individual component modules. Split when a component mixes API calls, permission policy, URL state, and visual composition, or when unrelated change requests routinely touch the same file.

## Architecture Decision Records

Record before irreversible implementation:

```text
docs/adr/001-hybrid-frontend-migration.md
docs/adr/002-auth-session-and-expiry-signalling.md
docs/adr/003-generated-api-and-domain-adapter-boundary.md
docs/adr/004-route-metadata-and-url-state.md
docs/adr/005-v2-design-system-and-css-isolation.md
docs/adr/006-ui-primitive-library-selection.md
docs/adr/007-form-architecture.md
docs/adr/008-data-table-architecture.md
docs/adr/009-rtl-and-localization-architecture.md
docs/adr/010-motion-policy.md
docs/adr/011-staff-portal-shared-design-system.md
docs/adr/012-portal-token-storage-security.md
```

## Proposed target tree (representative internals)

```text
src/
├── app/{providers/AppProviders.tsx,shell/{StaffShell,StaffSidebar,StaffTopBar}.tsx}
├── api/{generated/**,http/**,pagination.ts,queryClient.ts}
├── auth/{guards/**,AuthProvider.tsx,permissions.ts,session.ts}
├── design-system/
│   ├── foundations/{tokens.css,tokens.ts,motion.ts,icons.ts}
│   ├── primitives/{Button/Button.tsx,Input/Input.tsx,IconButton/IconButton.tsx}/
│   ├── composites/{Dialog,Menu,DataTable,FormField}/
│   └── patterns/{AsyncState,FilterBar,PageHeader}/
├── features/
│   ├── tickets/
│   │   ├── api/{tickets.ts,queues.ts,attachments.ts}
│   │   ├── model/{queryKeys.ts,useTicketListQuery.ts,useTicketMutations.ts,ticketUrlState.ts}
│   │   ├── ui/{list/TicketListScreen.tsx,detail/TicketDetailScreen.tsx,composer/TicketComposerForm.tsx}
│   │   ├── routes/TicketRoutes.tsx
│   │   └── __tests__/
│   ├── customers/{api/{customers.ts,duplicates.ts},model/{queryKeys.ts,customerUrlState.ts},ui/{list,detail,forms},routes,__tests__}/
│   ├── reports/{api/{reports.ts,exports.ts},model/{reportUrlState.ts,queryKeys.ts},ui/{ReportWorkspace,Dashboard,ReportFilterBar},routes}/
│   └── admin/{organisation,security,catalogue,sla,automation,channels,integrations,ai,data-protection,audit}/
├── i18n/{config.ts,LocaleProvider.tsx,locales/{en.json,ar.json}}
├── portal/{auth/**,shell/**,features/{tickets,help,account,guest,public}/,routes/**}
├── routes/{config/{staffRoutes.ts,portalRoutes.ts,navigation.ts},staff.tsx,portal.tsx,public.tsx,router.tsx}
├── shared/{async/**,attachments/{api,model,ui},hooks/**,lib/{urlState,bilingual,date}.ts,test/**}
└── styles/{reset.css,tokens.css,globals.css,legacy-v1.css}
```

## Old → new mapping

| Old | New | Strategy |
|---|---|---|
| `src/shell/Sidebar.tsx`, `TopBar.tsx`, `AppLayout.tsx` | `src/app/shell/**` | rewrite presentation; reuse navigation/filter/auth behaviour. |
| `src/portal/shell/PortalLayout.tsx` | `src/portal/shell/**` | rewrite with shared system and portal composition. |
| `src/components/ui/index.tsx` | `src/design-system/{primitives,composites,patterns}/**` | split behavioral contracts; rewrite styling; remove fallback maps. |
| `src/index.css` | `src/styles/{reset,tokens,globals,legacy-v1}.css` | isolate then retire V1 selectors. |
| `src/router.tsx` | `src/routes/**` | refactor configuration only; preserve URL/guard contracts. |
| `src/shell/navigation.ts` | `src/routes/config/navigation.ts` | extract typed metadata, derive navigation. |
| `src/features/tickets/api/wire.ts` | `src/features/tickets/api/**` | preserve/split domain operations; rewrite views. |
| `src/features/customers/api/wire.ts` | `src/features/customers/api/**` | preserve/split domain operations; rewrite views. |
| `src/features/admin/api/wire.ts` | `src/features/admin/<domain>/api/**` | split by domain before reuse. |
| `src/features/operations/*` | `features/operations/model/**` then specific domain modules | extract useful logic; do not migrate generic raw UI. |
| `src/shared/attachments/AttachmentUploader.tsx` | `shared/attachments/{model,ui}` | preserve upload/progress behavior, redesign labelled control. |
| `src/shell/{AsyncBoundary,states/**}` | `shared/async/**`, design-system state patterns | keep semantics, rebuild visual layer. |
| `src/pages/operations/{OperationsPages,CreationPages}.tsx` | feature route/UI modules | extract API/model per page; no giant replacement file. |
| `src/pages/admin/ChannelsPage.tsx` | `features/admin/channels/**` | split tabs/actions/transcript/forms. |
| `src/pages/DashboardPage.tsx` | none | retire only after confirming no external reference; `/dashboard` remains reports dashboard. |

# CODEX V2 GUARDRAILS

1. Never change backend contracts to solve frontend styling problems.
2. Never manually edit `src/api/generated/**`; change OpenAPI/Orval only through an approved contract workflow.
3. Never bypass `PERMISSIONS`, `ProtectedRoute`, `RequirePermission`, or action guards.
4. Never duplicate endpoint strings when a generated operation or named domain adapter exists.
5. Never place API calls, permission rules, route decisions, or business transforms inside design-system primitives.
6. Never hardcode user-visible text, ARIA labels, status labels, or Arabic fallback maps in V2 UI.
7. New UI supports LTR and RTL from creation through logical CSS and direction-aware interaction.
8. Every new screen includes loading, error, empty, forbidden/unauthorized, and populated states as applicable.
9. Prefer approved tokens and design-system variants over arbitrary colors, radii, shadows, z-indexes, and one-off Tailwind component styling.
10. Do not modify V1 implementation except in an explicitly approved extraction, route migration, compatibility, or retirement step.
11. Do not remove V1 code/CSS until the route migration contract and approval gate are satisfied.
12. Avoid unrelated dependency upgrades, formatting, generated-code changes, and backend changes.
13. Preserve Axios/CSRF/session/Query/error/invalidation behavior unless separately approved and tested.
14. Every migrated route must meet the migration contract.
15. V2 feature JSX must not call `apiRequest`, `httpClient`, or contain endpoint literals.
16. Portal bearer credentials must never reach staff endpoints; do not move portal session storage without backend security approval.
17. Preserve URLs and deep links; do not introduce permanent `/v2` routes.
18. Do not recreate an untyped generic CRUD screen from `CollectionPage`/`ResourceFormDialog`; extract domain contracts first.
19. Treat `V2 BLOCKER-001` as resolved only when its provider/mutator tests prove one controlled expiry path.
20. Read `design-system/MASTER.md` and any applicable `design-system/pages/*.md` before every V2 UI implementation task.

## Phase plan

| Phase | Objective | Code areas | Outputs | Prerequisites | Completion criteria |
|---|---|---|---|---|---|
| 0 — blockers/hardening | eliminate true integration blockers | auth/session/mutator tests | expiry fix design and tested correction; API boundary inventory | Gate 1 | `V2 BLOCKER-001` passes; no UI rewrite. |
| 1 — architecture foundation | establish boundaries/isolation | routes metadata, styles plan, i18n/test utilities | target module scaffolding rules and architecture tests | Phase 0, Gate 2 | V1 still works; imports/CSS policy enforceable. |
| 2 — design system | build approved foundations/primitives | design-system/styles/shared async | tokens, primitives, state/overlay/form/table contracts | Gate 3 | a11y/RTL/component tests and design approval. |
| 3 — shell + login POC | prove identity/auth/shell | auth UI, app shell, nav/routes | login/recovery frame and staff shell | Gate 4 | same auth/permissions/responsive/RTL behaviour. |
| 4 — tickets/workspace POC | prove core work surface | workspace/tickets and URL state | POC routes and parity suite | Gate 5 | Gate 6 accepts full POC. |
| 5 — customer-facing core | migrate customer/knowledge core | customers/knowledge/shared attachments | customer and knowledge V2 routes | POC accepted | route contracts pass. |
| 6 — administration | migrate admin by domain | admin adapters/forms/tables | organization/security/catalogue/SLA/etc. | core patterns proven | each domain individually certified. |
| 7 — reports/knowledge | complete analysis/content surfaces | reports/dashboard/schedules/remaining knowledge | report workspace and chart decision implementation | table/filter ADR | export limits represented truthfully. |
| 8 — portal/public surfaces | migrate portal and public channels | portal auth/shell/features/public | portal/guest/help/forms/chat routes | portal security ADR | staff isolation and portal workflows pass. |
| 9 — V1 retirement | remove verified legacy | old pages/UI/CSS/config candidates | retirement PRs and final test evidence | Gate 7 | no V1 imports/legacy CSS paths remain; build/test passes. |

## Stop points / approval gates

1. **Gate 1 — Architecture approved:** do not change runtime code before this blueprint is accepted.
2. **Gate 2 — Technology/library choices approved:** do not install Radix, React Hook Form, icons, testing, table, chart, or motion dependencies before explicit approval.
3. **Gate 3 — Design/branding direction approved:** do not implement visual identity/tokens/shell aesthetics before approval.
4. **Gate 4 — Design system approved:** do not migrate production routes before primitive/pattern contracts and tests are accepted.
5. **Gate 5 — Login + shell approved:** do not replace workspace/tickets until auth/shell behavior and visual direction are accepted.
6. **Gate 6 — Ticket POC approved:** do not begin broad feature migration until tickets/workspace parity is accepted.
7. **Gate 7 — Full migration approved:** do not migrate remaining route groups or retire V1 without explicit authorization.

# NEXT ACTION

After this blueprint is reviewed, perform exactly one step: **resolve and test `V2 BLOCKER-001` using the existing session event authority.**

It would touch `src/api/http/mutator.ts`, `src/auth/session.ts` only if needed to expose the selected event path, `src/auth/AuthProvider.tsx` only if needed to consume it, and narrowly scoped tests under `src/auth/__tests__/` or the existing test structure. It must not touch routes, design-system code, page layouts, Tailwind configuration, OpenAPI generation, portal storage, dependencies, or backend code. Deliverable: a tested, documented single staff-session expiry flow. Validation: lint, typecheck, relevant Vitest tests, and the normal production build.

## Final decision snapshot

```text
Strategy: Hybrid rebuild preserving integration/domain foundations and replacing presentation progressively.
Migration mechanism: One application; route-by-route component replacement with V1 CSS quarantine and no permanent /v2 URLs.
API strategy: Preserve Orval/Axios/mutator/Query; route all UI consumption through named feature domain adapters and hooks.
Auth strategy: Preserve staff cookie/CSRF/2FA and separate portal bearer semantics; fix staff expiry signalling first.
Routing strategy: Restructure router metadata internally while preserving all current URLs and guards.
Design-system strategy: New shared token → primitive → composite → pattern system, governed by design-system/MASTER.md.
Form strategy: PROPOSED React Hook Form + existing Zod, server errors authoritative; approval required.
Table strategy: Shared semantic DataTable + mobile DataList; evaluate TanStack Table later; no AG Grid for POC.
RTL strategy: i18next preserved, semantic keys, logical CSS, direction-aware icon/interaction tests.
Motion strategy: Tokenized, purposeful CSS-first motion; Framer Motion conditional and approval-gated.
Portal strategy: One branded system with portal-specific compositions; retain isolated bearer auth pending backend security decision.
First POC routes: /login, /workspace, /tickets, /tickets/:ticketId (and /tickets/new only if needed to prove forms).
First blocker: V2 BLOCKER-001 — mutator browser event and AuthProvider in-memory emitter mismatch.
Next approval gate: Gate 1 — Architecture approved.
```
