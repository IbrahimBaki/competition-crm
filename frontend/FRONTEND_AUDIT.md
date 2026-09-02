# Frontend Audit — Support CRM

**Audit scope:** read-only review of the complete `frontend/src` tree (578 files), configuration, generated API client, test setup, and representative pages/features. No application code, dependency, configuration, or backend changes were made. This report reflects the source state inspected on 2026-09-02. The worktree already contained unrelated uncommitted changes; they were not altered.

**Runtime note:** a browser runtime review was not performed. Playwright is configured for `http://app.competition-crm.azmsquad.localhost:5174`, but this audit did not start a development server or authenticate against a potentially stateful local backend. Source inspection is sufficient for the findings below.

## 1. Project Overview

| Area | Current technology | Version | Notes |
| --- | --- | --- | --- |
| Framework | React | 18.3.1 | SPA rendered from `src/main.tsx` |
| Language | TypeScript | 5.3.3 | Strictness is governed by `tsconfig.json`; generated API models are TS |
| Build tool | Vite | 5.0.8 | React plugin; manual `vendor` and `api-generated` chunks in `vite.config.ts` |
| Package manager | npm | lockfile present | `package-lock.json`; no pnpm/yarn lockfile |
| Node requirement | Not determined | — | No `engines`, `.nvmrc`, or `.node-version` found; dev types target Node 20 |
| Routing | React Router DOM | 6.20.1 | Data router via `createBrowserRouter` |
| Server state | TanStack React Query | 5.28.0 | Generated hooks plus handwritten queries |
| HTTP | Axios | 1.6.5 | Cookie/CSRF staff auth; bearer-token portal auth |
| API generation | Orval | 6.21.0 | Generated from `../docs/api/openapi.yaml` |
| Forms/validation | Native React state + HTML validation + Zod | 3.22.4 | Zod is installed; no React Hook Form/Formik; most forms rely on server validation |
| Styling | Tailwind CSS + global CSS | 3.4.1 | Tailwind utility classes coexist with `src/index.css` UI classes |
| i18n | i18next / react-i18next | 23.7.6 / 14.0.0 | English and Arabic JSON bundles |
| Testing | Vitest, Testing Library, MSW, Playwright | see `package.json` | 29 test files and three E2E specs |
| Charts / icons / animation | No dedicated library | — | Inline SVG, CSS animation, and Tailwind transitions only |

Core production dependencies are compact and appropriate. `package.json` exposes lint, typecheck, unit test, Playwright, build, and API regeneration scripts. Vite uses `VITE_API_BASE_URL`, `VITE_API_PREFIX`, and `VITE_APP_NAME` (`.env.example`); no secret values are documented here.

## 2. Application Architecture

### Important structure

```text
src/
├── api/
│   ├── http/                 Axios, CSRF, envelope/error normalization, mutator
│   ├── generated/            Orval clients/models, split by backend tag
│   ├── pagination.ts
│   └── queryClient.ts
├── auth/                     staff session, provider, permission guards
├── components/ui/            compact shared primitive set (single index.tsx)
├── features/
│   ├── admin/                organisation, security, SLA, automation, channels
│   ├── customers/            list, detail, duplicate merge, adapters
│   ├── knowledge/
│   ├── operations/           generic collection and resource-form abstractions
│   ├── reports/
│   ├── tickets/              list, detail panels, mutations, adapters
│   └── workspace/            landing, queues, tasks, notifications, replies
├── i18n/                     `en.json`, `ar.json`, language/direction provider
├── pages/                    route composition; admin, auth, portal, public, reports
├── portal/                   isolated portal auth/session/layout
├── shared/                   attachment uploader and confirmation dialog
├── shell/                    staff shell, navigation, boundaries, reusable states
├── main.tsx                  provider composition
├── router.tsx                complete route declaration
└── index.css                 tokens, primitive styles, app-shell and responsive CSS
```

The architecture is primarily **feature-based**, with a sensible cross-cutting layer for API, auth, shell, UI primitives, and i18n. Generated code is kept separate from handwritten adapters (`features/*/api/wire.ts`), a strong boundary for preserving backend contracts.

It is not uniformly modular. The newer pattern uses shared `AsyncBoundary`, `PageHeader`, `Card`, `Field`, `Dialog`, generic `CollectionPage`, and feature hooks. Older/direct patterns retain bespoke tables, raw Tailwind controls, inline endpoints, and page-local state. Examples include `features/operations/CollectionPage.tsx` versus `features/admin/organisation/BranchListTable.tsx`, `DepartmentListTable.tsx`, and `TeamListTable.tsx`; and `pages/TicketsPage.tsx` versus `pages/admin/ChannelsPage.tsx`.

No circular import was established from static inspection. The main scaling risks are responsibility concentration in `components/ui/index.tsx` (about 10.8 KB), `pages/operations/OperationsPages.tsx` (about 19 KB), `pages/operations/CreationPages.tsx` (about 10.9 KB), `features/admin/api/wire.ts` (about 26.9 KB), and large generated clients. Generated size is expected and should not be treated as UI complexity.

## 3. Route Inventory

All staff routes are children of `/`, wrapped by `ProtectedRoute` and `AppLayout`; finer permission gates are declared in `src/router.tsx`. “Authenticated” means the parent guard only.

| Route | Page / component | Layout | Auth required | Permission | Notes |
| --- | --- | --- | --- | --- | --- |
| `/login` | `LoginPage` | Public | No | — | Staff cookie login |
| `/login/two-factor` | `TwoFactorPage` | Public | Pending 2FA | — | Challenge completion |
| `/forgot-password`, `/reset-password`, `/invitations/:token` | recovery pages | Public | No | — | Password/invitation flows |
| `/`, `/workspace` | `WorkspacePage` | Staff shell | Yes | authenticated | Four operational queue panels |
| `/tickets`, `/tickets/:ticketId` | ticket list/detail | Staff shell | Yes | any ticket view scope | Detailed workbench |
| `/tickets/new` | `NewTicketPage` | Staff shell + route dialog | Yes | `tickets.create` | Modal route wrapper |
| `/customers`, `/customers/:customerId` | customer list/detail | Staff shell | Yes | `customers.view` | Detail adds component guards |
| `/customers/new` | `NewCustomerPage` | Staff shell + route dialog | Yes | `customers.create` | Modal route wrapper |
| `/knowledge`, `/:articleId` | knowledge list/article | Staff shell | Yes | article view | Lifecycle/categories are page features |
| `/knowledge/new`, `/:articleId/edit` | article create/edit | Staff shell + route dialog | Yes | create/update | CRUD form |
| `/account` | `StaffAccountPage` | Staff shell | Yes | authenticated | 2FA controls |
| `/reports`, `/reports/:reportId`, `/dashboard` | report pages | Staff shell | Yes | any report view scope | “Dashboard” is report-driven |
| `/report-schedules` | `ReportSchedulesPage` | Staff shell | Yes | schedule manage | |
| `/admin` | `AdminIndexPage` | Staff shell | Yes | authenticated only | Potentially broader than navigation |
| `/admin/branches`, `/:branchId` | branch pages | Staff shell | Yes | branch view any | includes calendar/working hours |
| `/admin/departments`, `/:departmentId` | department pages | Staff shell | Yes | department view any | |
| `/admin/teams`, `/:teamId` | team pages | Staff shell | Yes | team view any | |
| `/admin/users` | users | Staff shell | Yes | user manage | invitations/lifecycle/placement |
| `/admin/roles`, `/:roleId` | roles | Staff shell | Yes | role manage | role editor |
| `/admin/ticket-catalogue` | catalogue | Staff shell | Yes | statuses **or** categories manage | tabs/panels |
| `/admin/sla-policies` | SLA policies | Staff shell | Yes | SLA view | individual actions further guarded |
| `/admin/automation-rules` | automation | Staff shell | Yes | automation view | |
| `/admin/channels` | channels | Staff shell | Yes | any channel-view scope | Email, forms, templates, chat tabs |
| `/admin/settings`, `/admin/audit`, `/admin/data-protection`, `/admin/integrations`, `/admin/ai` | administration operations | Staff shell | Yes | corresponding scope(s) | pages assembled from `OperationsPages.tsx` |
| `/portal/login`, `/register`, `/verify` | portal auth pages | Portal layout | No | — | Separate bearer auth |
| `/portal/track/:token`, `/help`, `/help/:slug` | guest portal pages | Portal layout | No | — | Public ticket/help content |
| `/portal/tickets`, `/new`, `/:id`, `/account` | portal pages | Portal layout | Yes | portal session | Customer-facing support surface |
| `/forms/:formKey`, `/forms/submissions/:trackingToken`, `/chat` | public channel pages | Public/no staff shell | No | — | Web-form submission/status and live chat |
| `*` | `NotFoundState` | none | — | — | Global 404 |

There are no router-declared breadcrumbs. Route errors for the staff parent use `RouteErrorBoundary`; the portal parent has no router `errorElement`. Route nesting is clear and redirects are guard-driven (`Navigate` to login or 2FA). The legacy-looking `pages/DashboardPage.tsx` is not imported by the router, while `/dashboard` maps to `ManagementDashboardPage`; this is a dead/legacy candidate.

## 4. Authentication & Authorization

### Staff

`AuthProvider` (`src/auth/AuthProvider.tsx`) bootstraps a cookie session with `/sanctum/csrf-cookie` then `/auth/me`. It stores only the current user object in module memory (`src/auth/session.ts`), not a staff token in browser storage. Login posts `/auth/login`, detects a two-factor response, then reloads `/auth/me`; logout posts `/auth/logout`, clears React Query and redirects. The provider revalidates on focus/visibility at most once every 30 seconds. A 429 bootstrap receives one capped retry.

`apiRequest` handles `419` by refreshing CSRF and retrying once, then tries a deduplicated session recovery on staff 401s. Permission keys arrive in `user.permission_keys`; `PERMISSIONS`, `hasPermission`, `hasAnyPermission`, `hasAllPermissions`, `usePermissions`, `RequirePermission`, `ActionGuard`, route gates, and `useVisibleNavigation` enforce them in several layers. This is robust and portable.

One implementation concern: `apiRequest` dispatches a browser `session:expired` event (`src/api/http/mutator.ts`), while `AuthProvider` subscribes to the separate in-memory emitter exported by `src/auth/session.ts`. Unless another listener bridges them (none found), that specific expired-session redirect path may not execute. This is an integration risk, not a reason to rewrite the auth model.

### Portal

Portal login (`src/portal/auth/PortalAuthProvider.tsx`) receives a bearer token. `src/portal/auth/portalSession.ts` persists that token and basic portal user profile in `localStorage`; the mutator attaches it only for `/portal/*`, deliberately avoiding staff leakage. Logout explicitly carries the bearer token. This separation is correct, but localStorage bearer tokens have higher XSS impact than the staff cookie session; a rebuild must retain the backend’s intended portal model while minimizing XSS exposure.

**Rebuild implications:** preserve the providers, transport rules, permissions/constants, and route metadata; consolidate session-expiry signalling and retain server authorization as the ultimate source of truth. Client checks are navigation/UI controls, not authorization guarantees.

## 5. Backend API Integration

**Configuration:** `src/api/http/client.ts` builds Axios `baseURL` from `VITE_API_BASE_URL + VITE_API_PREFIX`, with credentials/XSRF enabled and JSON acceptance. CSRF calls use only `VITE_API_BASE_URL` (`csrf.ts`). The documented contract source is `../docs/api/openapi.yaml`; `orval.config.ts` generates tag-split React Query hooks and models into `src/api/generated/` and routes all generated requests through `apiRequest`.

The mutator centralizes locale headers, response envelope unwrapping, paginated envelope handling, normalized errors, CSRF retry, and staff session recovery. `queryClient.ts` has a 30-second stale time, targeted no-retry error classes, two retries otherwise, no window-focus refetch, and no mutation retry. There is no WebSocket/SSE implementation or polling found. Downloads use Axios blobs in portal ticket detail; uploads use generated attachment endpoint plus `onUploadProgress` in `shared/attachments/AttachmentUploader.tsx`. Lists generally use server pagination, filters, sorting, and cancellation signals.

Handwritten `apiRequest` calls do exist in pages/features despite `src/api/README.md` advising feature code to use generated hooks. They are especially concentrated in `pages/operations/CreationPages.tsx`, `pages/operations/OperationsPages.tsx`, portal/public pages, `features/operations/*`, and adapter files. This is not uncontrolled scatter—the shared transport is still used—but it weakens full contract typing and duplicates endpoint strings.

| Module | API service / boundary | Main operations | Used by |
| --- | --- | --- | --- |
| Auth/security | `auth/*`, generated `security`, admin wire | session, 2FA, users, roles, invitation | shell, staff/admin pages |
| Organization | generated `organization`, admin wire/hooks | branches, departments, teams, calendars | admin pages/details |
| Ticketing | generated `ticketing`, `features/tickets/api/wire.ts` | queues, tickets, messages, transitions, assignments, watchers | tickets/workspace |
| Customers | generated `customers`, `features/customers/api/wire.ts` | list/detail, contacts, notes, merge, ERP context | customer pages |
| Workspace | generated `workspace`, workspace wire/hooks | queues, tasks, quick replies | workspace/ticket workbench |
| Knowledge | generated `knowledge` + direct calls | articles, categories, lifecycle/render | knowledge and portal help |
| Reporting | generated `reporting`, report wire | definitions, data, export/schedules | reports/dashboard |
| Administration | generated automation/channels/SLA/AI/integrations/observability | CRUD and operational actions | admin pages |
| Notifications | generated notifications + feature wire | list/read actions | `NotificationBell/Menu` |
| Attachments | generated attachments + uploader | multipart upload/link/download | tickets/customers/portal |
| Portal/public | generated portal plus direct mutator calls | portal auth/tickets/messages/feedback; web forms/chat | portal and public pages |

**API Layer Reusability Score: 8/10.** The contract-generated clients, models, envelope/error handling, query defaults, CSRF logic, authorization transport, and handwritten domain adapters are strong rebuild assets. Cleanup should move remaining endpoint literals into generated/domain service wrappers and standardize mutation invalidation. No API secret is embedded in source inspected.

## 6. State Management

TanStack Query owns most server state. Query hooks and local feature hooks hold list filters/pagination; `TicketsPage` keeps queue mode and selection locally, `CustomersPage` keeps filter state in its feature hook, and generic `CollectionPage` uses local page/search/sort. React Context is deliberately limited to staff auth, locale, portal auth, and toast messages. There is no Redux, Zustand, or global client-state store.

URL state is underused: ticket/customer filters, selected queues, report filters, and pagination are largely component-local rather than shareable/deep-linkable. Saved ticket views use localStorage (`SavedViewsBar.tsx`). Portal and public chat also store browser-side session data. Query data can be duplicated where generated hooks and handwritten calls coexist, but key naming is mostly understandable.

**State Architecture Quality: 7/10.** The server/client split is good and avoids oversized global state. A redesign should retain Query and make significant list/report state URL-backed; extract scattered local storage conventions into named adapters.

## 7. Forms

Forms use native controlled inputs, `Field`, `FormErrorSummary`, `Input`, `Select`, `Textarea`, and per-page `useState`; there is no unified form engine. Server errors pass through `normaliseApiError` and display at fields in many newer forms. Browser constraint validation supplies basic required/type/min/max behavior. Zod is installed but not a visible application-wide validation strategy.

Major forms include staff/portal login and recovery (`pages/LoginPage.tsx`, `pages/auth/RecoveryPages.tsx`, `pages/portal/*Auth*`), ticket/customer/knowledge creation (`pages/operations/CreationPages.tsx`), ticket composer with attachments, staff 2FA, branch/department/team forms, role editor, generic `ResourceFormDialog`, web forms/chat transfers, SLA policy, automation, and public forms/chat.

Reusable `ResourceFormDialog.tsx` covers simple schema-like CRUD fields, JSON and CSV parsing. It is helpful but increasingly fragile for domain-rich forms: field semantics, client validation, translation, async options, typed payloads, and accessibility details sit in configurations or separate bespoke components. The three organization forms are materially similar yet duplicated. File upload is reusable but uses a visible raw file input without an associated label.

**Form Logic Reusability: 7/10.** Server validation mapping, attachment flow, generic field primitives, and domain payload transformations survive. Presentation and local-state handling should be rebuilt behind a typed form abstraction; business behavior does not need a ground-up rewrite.

## 8. Tables & Data-Heavy UI

There is no data-grid library. Tables are native HTML with server pagination in ticket/customer/portal/generic collections. Ticket list supports server filtering, search, sort, queue modes, saved views, row selection, and bulk actions. Customer list supports search/status/sort. `CollectionPage` supplies generic page/search/sort/delete/table/pagination behavior, and its `.collection-table` mobile transformation relies on `data-label` attributes.

Many independent native table implementations remain: `TicketListTable.tsx`, `CustomerListTable.tsx`, organization/user/catalogue tables, reports, management dashboard, knowledge categories, and generic collections. Some implement responsive table behavior while others only add `overflow-x-auto`; mobile quality is therefore inconsistent. Export exists for reports through `ExportMenu`, but its own code documents that no export status/download API exists. No table virtualization, column configuration, reusable bulk toolbar, or universal selection model exists.

**Data UI Reusability: 6/10.** The ticket data model and collection abstraction are useful. A rebuild should introduce one accessible, responsive table/list primitive with composable filter/pagination/selection APIs, then migrate specialized cells/actions.

## 9. Existing UI Component System

| Component | Location | Reusable? | Widely used? | Quality | Replace / keep |
| --- | --- | ---: | ---: | --- | --- |
| Button, Card, Badge | `components/ui/index.tsx` | Yes | Moderate | Small typed primitives | Keep API; split/refresh styles |
| Field/Input/Select/Textarea | same | Yes | Moderate | Labels/errors are strong | Keep API; extend variants |
| Dialog | same | Yes | Moderate | Escape, focus loop, focus restore included | Keep behavior; refine design |
| Toast | same | Yes | Moderate | Basic live region/timeout | Keep behavior; improve action/accessibility |
| Pagination/Skeleton/PageHeader | same | Yes | Moderate | Useful baseline | Keep/rebuild visual layer |
| AsyncBoundary and states | `shell/AsyncBoundary.tsx`, `shell/states/*` | Yes | High | Repeated loading/error/empty protections | Definitely retain |
| CollectionPage/ResourceFormDialog | `features/operations/*` | Partly | Admin-heavy | Valuable abstraction, weak type/semantic reach | Reuse after cleanup |
| AttachmentUploader | `shared/attachments/*` | Yes | Limited | Correct progress/integration concern | Keep logic; redesign control |
| Sidebar/TopBar/PortalLayout | `shell/*`, `portal/shell/*` | Shell-specific | High | Functionally sound but visual duplication | Rebuild structurally |

There is a real but compact primitive system, reinforced by CSS classes in `index.css`. It is not yet a complete design system: no composable menu/dropdown primitive, icon system, table family, breadcrumb, tabs, drawer abstraction, theme token layer, date picker, tooltip, or documented component contracts. At least 123 TSX files carry direct `className` usage, and many bypass the primitives.

## 10. Styling Architecture

Tailwind is globally configured with no theme extension (`tailwind.config.js`), while `src/index.css` defines a small semantic token set (primary/background/foreground/card/muted/border/status, radii, shadows, header height), shared app-shell, control, dialog, table, mobile, RTL, and reduced-motion rules. It imports Plus Jakarta Sans and agrees broadly with `design-system/MASTER.md` (blue primary, orange accent, light surface, same typography), though the implemented accent is `#c2410c` rather than the master’s `#EA580C` and many hard-coded slate/gray/blue utilities bypass tokens.

Evidence of split styling: global `.ui-button` and `.ui-input` coexist with raw `bg-blue-600`, `border-gray-300`, `rounded`, `rounded-lg`, `rounded-xl`, `shadow`, and arbitrary layout utilities throughout, such as `pages/TicketsPage.tsx`, `features/admin/organisation/*Form.tsx`, `pages/reports/ManagementDashboardPage.tsx`, and portal pages. Raw colors also appear directly in `index.css`. Only four TSX files use inline `style`, chiefly the legitimate upload progress width and knowledge tree indentation.

Dark mode is not implemented. A z-index scale is informal (e.g. top bar 30, scrim 50, sidebar 60, dialog 80, toast 100, skip link 1000), not a documented token. Breakpoints use Tailwind defaults alongside bespoke 1023/639/419 CSS breakpoints.

**Design System Maturity: 5/10.** Tokens and primitives exist, but the token contract has not become the default authoring path.

## 11. Visual Consistency Audit

The source shows meaningful baseline quality—clear hierarchy in shared page headers, consistent shell colors, readable form controls—but it also shows clear independently styled-page drift:

- Direct button styles recur instead of `Button`: ticket queue tabs in `pages/TicketsPage.tsx`; organization forms; user table actions; customer panels; reports.
- Direct tables use different density, headers, status badges, and mobile strategies (`features/admin/organisation/*ListTable.tsx`, `features/tickets/list/TicketListTable.tsx`, `features/operations/CollectionPage.tsx`, `pages/reports/ManagementDashboardPage.tsx`).
- Card/radius/shadow vocabulary ranges from global `--radius-*` to Tailwind `rounded`, `rounded-lg`, `rounded-xl`; management dashboard uses plain white `rounded-lg shadow`, whereas shared Card uses a tokenized border/shadow.
- The staff shell follows global token classes but portal layout is mostly raw Tailwind, producing two visual products (`src/shell/*` vs `src/portal/shell/PortalLayout.tsx`).
- The only sidebar icon is a repeated generic SVG shape, not a meaningful consistent icon set (`shell/Sidebar.tsx`). Text glyphs (`×`, `⌄`, and `⏳` in `ExportMenu.tsx`) conflict with the design master’s SVG-icon direction.
- `DashboardPage.tsx` has “Dashboard content coming soon...” but is not routed. It is evidence of legacy presentation rather than current product UI.

The application does not appear to have uncontrolled CSS chaos; its weakness is an incomplete migration to the newer system.

## 12. Login Page Audit

`pages/LoginPage.tsx` combines staff email/password state, `useAuth().login`, normalized error display, busy state, browser required/email validation, i18n for core labels, and links to recovery/portal. It uses `Card`, `Field`, `Input`, and `Button`; it is responsive through a centered `max-w-md` layout. No logo asset exists: branding is an environment name and “Operations workspace” text. There is no password visibility toggle, explicit autocomplete attributes, or client-side schema validation.

Preserve: `AuthProvider.login`, normalized error handling, 2FA route handoff, requested-route restoration, and accessible `Field` behavior. Rebuild: the complete visual frame/brand treatment, login content structure, text localization completion, password affordance, and any recovery/portal discovery experience.

## 13. Main Application Shell

Staff navigation is centralized in `shell/navigation.ts`; `useVisibleNavigation.ts` filters the tree by user permissions. The responsive shell (`AppLayout`, `Sidebar`, `TopBar`) has a skip link, focus-on-route-change main content, 17rem desktop sidebar, mobile slide-out sidebar/scrim at <=1023px, locale toggle, notifications, and user/logout menu. Menu labels use i18n keys, but brand/product copy is hard-coded in `Sidebar.tsx` and `TopBar.tsx`; no breadcrumbs, global search, theme selector, or notification center route exists.

Portal has a completely separate header/nav/footer. Its menus depend on portal authentication, not roles. Staff nav correctly depends on permission configuration rather than backend menu data.

**Assessment: completely rebuild the presentation and shell composition, preserve the navigation metadata and permission-filter logic.** The existing shell is functional and accessibility-aware, but a premium redesign needs a coherent icon language, menus with outside-click/focus semantics, breadcrumbs, mobile navigation interaction, and aligned portal branding.

## 14. Responsive Design

Positive evidence: `index.css` has desktop/tablet/mobile shell rules, logical RTL properties, 320px body minimum, responsive header/actions/pagination, a transformed generic mobile table, and `prefers-reduced-motion`. Tailwind responsive grids occur throughout (`sm`, `md`, `lg`). Ticket and customer detail use responsive grid ordering; portal layouts constrain widths.

Risks: only generic `CollectionPage` and knowledge tables receive the card-like mobile table conversion. Many other wide tables merely use `min-w-full`/horizontal wrappers or no explicit table strategy. Fixed desktop columns in `TicketDetailPage.tsx` (`280px`, `320px`) collapse at `lg` but receive no intermediate density strategy. Portal’s horizontal nav has no small-screen overflow/wrap rule. Bespoke breakpoint values mix with Tailwind. E2E responsive coverage exists (`e2e/responsive.spec.ts`) but only a desktop Playwright project is configured.

**Responsive Architecture: 6/10.** Strong shell groundwork; data-heavy modules need systematic mobile rules and actual cross-viewport validation.

## 15. RTL / LTR & Localization

i18next loads `src/i18n/en.json` and `ar.json`; `LocaleProvider` persists locale and updates document `lang`/`dir`. CSS uses logical properties in important global layout areas, and the shell reacts to RTL sidebar/menu direction. Generated API requests carry `Accept-Language`. Bilingual helpers exist in tickets/customers/public/knowledge.

However, only 109 source files use i18n translation calls while the TSX string search found roughly 1,260 string literals. Many visible user-facing strings remain hard-coded, especially portal pages, `OperationsPages.tsx`, admin forms/dialogs, collection/resource abstractions, raw status labels, toast text, login links, and reports. `components/ui/index.tsx` uses hard-coded Arabic field/page-title mapping tables rather than translation keys, which cannot scale. Estimated properly localized UI text: **roughly 55–65%**, based on source patterns, not a rendered-string census.

**Localization Readiness: 6/10.**  
**RTL Readiness: 6/10.**

The foundation is real; a redesign must make translation keys mandatory for all new UI, remove mapping-table fallbacks, use logical CSS everywhere, and test Arabic content expansion/functionality.

## 16. Accessibility

Strengths include skip links, visible global `:focus-visible`, semantic heading/page-header patterns, `Field` label associations, error descriptions/alerts, `FormErrorSummary` focus, dialog focus containment/Escape/focus restore, live toast region, native tables with scopes in many places, `aria-live`/busy in shared controls, and reduced motion CSS.

High-risk patterns:

- `TopBar.tsx` user menu has `role="menu"` but menu item semantics/keyboard roving/focus management and outside-click dismissal are missing; the trigger lacks an accessible name beyond visual content.
- `PortalLayout.tsx` uses an unlabeled brand/navigation button; many controls have raw text but navigation/menu interaction is incomplete.
- `AttachmentUploader.tsx` uses a raw file input without a visible associated label, and its removable attachment buttons are visually small.
- Several raw buttons/links have inconsistent focus/target sizing; direct components bypass the shared 44px `ui-button` minimum.
- Table mobile conversions are not universal; some scroll-only tables will be difficult to operate/read on small screens.
- Hard-coded English ARIA labels and strings make localized assistive output inconsistent.
- Color/status labels generally include text, but not universally verified for contrast.

**Accessibility: 6/10.** The shared layer is better than the page-level consistency. A rebuild should preserve its good semantics and introduce automated axe/browser keyboard checks.

## 17. Animation & Interaction Layer

No Framer Motion, GSAP, or dedicated animation package is present. Interaction uses CSS transitions, spinner and skeleton keyframes, sidebar transform, hover color effects, upload progress, and a global reduced-motion override. This is restrained and generally appropriate, but lacks motion tokens and component-level consistency. `ExportMenu.tsx` uses an hourglass glyph while loading rather than the shared spinner.

**Motion / Interaction Quality: 4/10.** Functional feedback exists; there is no intentional product interaction language, page transition strategy, or common micro-interaction toolkit.

## 18. Branding

Current branding is minimal: application name from `VITE_APP_NAME`, hard-coded “Support CRM”/“Support Portal”, text “S” brand marks, Plus Jakarta Sans, blue/slate palette, and generic inline SVG. No `src/assets` directory, favicon strategy, logo files, illustrations, product imagery, or centralized brand module was found. `design-system/MASTER.md` specifies the emerging identity but source adoption is partial.

**Brand System Maturity: 3/10.** A full visual identity and asset system need creation; there is little existing branding to constrain a redesign.

## 19. Dashboard & Visualization Layer

The active dashboard is `pages/reports/ManagementDashboardPage.tsx`, a report-definition-driven totals-card and table screen with required filters. Workspace landing is an operations overview of My Tickets, department queue, SLA risk, and overdue tasks. There is no chart package or custom chart implementation; no graph/chart visualization is present. `DashboardPage.tsx` is an unused placeholder.

**Dashboard Architecture: 5/10.** Report data/filter logic is reusable, but presentation is basic, manually styled, non-visual, and not a shared widget system.

## 20. Component Complexity

Approximate complexity combines file size, responsibilities, conditional UI, data interaction, and state—not cyclomatic measurement.

| File | Approx. complexity | Main problems | Refactor difficulty |
| --- | ---: | --- | --- |
| `features/admin/api/wire.ts` | High | many adapters/endpoints/transforms | Medium |
| `pages/operations/OperationsPages.tsx` | High | seven disparate pages in one file, direct API/UI | High |
| `pages/operations/CreationPages.tsx` | High | multiple create/edit/detail forms, direct calls | High |
| `components/ui/index.tsx` | High | entire primitive library + i18n mappings in one file | Medium |
| `features/admin/security/RoleEditor.tsx` | High | permission editing/form/queries | Medium |
| `features/tickets/detail/TicketComposer.tsx` | High | messages, attachments, drafts, mutations | Medium |
| `pages/TicketsPage.tsx` | High | queue mode, list state, selection, permissions | Medium |
| `pages/admin/ChannelsPage.tsx` | High | four domains/tabs/dialogs/actions | High |
| `features/tickets/list/TicketFilterBar.tsx` | High | many filter controls/permissions | Medium |
| `features/customers/api/wire.ts` | High | hand-mapped customer domain contracts | Medium |
| `features/customers/duplicates/MergePreviewDialog.tsx` | Medium-high | merge preview/selection/action | Medium |
| `pages/public/PublicChannelPages.tsx` | Medium-high | three public flows in one file | High |
| `pages/portal/PortalTicketDetailPage.tsx` | Medium-high | detail/messages/download/feedback | Medium |
| `features/operations/CollectionPage.tsx` | Medium-high | generic fetch/filter/table/delete | Medium |
| `features/operations/ResourceFormDialog.tsx` | Medium-high | generic dynamic form + parser/mutation | Medium |
| `pages/reports/ManagementDashboardPage.tsx` | Medium | query/filter/totals/table local presentation | Low-medium |
| `auth/AuthProvider.tsx` | Medium | bootstrap, focus retry, 2FA, logout/session state | Medium |
| `pages/TicketDetailPage.tsx` | Medium | dense composition of 12 panels | Low-medium |
| `pages/CustomerDetailPage.tsx` | Medium | dense composition/adaptation/permissions | Low-medium |
| `features/customers/detail/CustomerIdentityPanel.tsx` | Medium | contacts editor and mutations | Medium |

The closest “God Components” are `OperationsPages.tsx`, `CreationPages.tsx`, `ChannelsPage.tsx`, and the single UI barrel. They should be migration boundaries, not reasons to discard their domain knowledge.

## 21. Code Duplication

- Organization branch/department/team forms and tables repeat visual/control/mutation patterns (`features/admin/organisation/*Form.tsx`, `*ListTable.tsx`).
- Ticket/customer lists each hand-roll surrounding pagination controls despite shared `Pagination`; many admin tables do too.
- Raw Tailwind buttons, inputs, cards, badges, alerts, and dialog structures duplicate `components/ui` counterparts.
- Local form state, busy/error handling, idempotency headers, query invalidations, and toast copy repeat in pages.
- Localized-value selection appears in multiple modules (`tickets/utils/bilingual.ts`, collection display, portal/public pages, operations pages).
- Permission patterns are centralized in constants/hooks but repeated declaratively at route/page/component layers; defense in depth is valid, but configuration could be derived from one route/action metadata source.
- API endpoint strings remain duplicated across direct `apiRequest` calls and generated/adapted services.

## 22. Technical Debt

### Critical

- Session-expiry event mismatch between `api/http/mutator.ts` browser dispatch and `auth/session.ts` emitter subscription risks an unauthenticated UI state without the intended controlled redirect.
- Portal bearer token is persisted in `localStorage` (`portal/auth/portalSession.ts`), raising XSS impact. It may be required by backend design, but must be a conscious security decision.
- Inconsistent typed API usage: direct requests in UI can drift from the OpenAPI generation rule documented in `src/api/README.md`.

### Important

- UI system is only partly adopted; direct styles/tables/forms make visual redesign expensive and regression-prone.
- i18n is incomplete and uses hard-coded Arabic maps in the UI primitive module.
- Local list/report state is rarely URL-backed, impairing shareable/reload-stable views.
- Report export status/download is explicitly missing in `features/reports/types.ts` and `features/reports/api/wire.ts` because the backend route is absent.
- `OperationsPages.tsx`, `CreationPages.tsx`, and `ChannelsPage.tsx` mix domain business actions and presentation.

### Cosmetic

- Generic/duplicated SVG and text-glyph icons; absent brand assets.
- Inconsistent radius/shadow/color utility choices and occasional raw English strings.
- Missing centralized breadcrumb/theme/icon APIs.

## 23. Dead / Legacy Code

- `src/pages/DashboardPage.tsx` is not referenced by `src/router.tsx` and contains “Dashboard content coming soon...”; `/dashboard` instead uses `ManagementDashboardPage`.
- `vite.config.js` and `vite.config.d.ts` coexist with authoritative `vite.config.ts`; their runtime role was not determined, so they should not be removed solely from this audit.
- Playwright output (`playwright-report/`, `test-results/`) is checked into the working tree; it is generated test artifact rather than application source.
- TODOs explicitly identify backend/API gaps or unfinished metadata: reports export status/download, ERP client generation, permission descriptions, and admin branding/default-locale endpoints.
- No conclusively unused components/assets beyond the dashboard were determined from static inspection. Generated files must not be manually deleted.

## 24. Dependency Health

| Classification | Dependencies | Assessment |
| --- | --- | --- |
| Core / necessary | React, React DOM, Vite, TypeScript, React Router | appropriate SPA baseline |
| Core / necessary | Axios, TanStack Query, Orval | strong typed transport/server-state combination |
| Core / necessary | i18next/react-i18next | required for bilingual product |
| Core / necessary | Tailwind/PostCSS/autoprefixer | current utility/global CSS implementation |
| Core / necessary | Vitest/Testing Library/MSW/Playwright | meaningful unit/integration/E2E toolchain |
| Replaceable but not redundant | Zod | retained dependency with no dominant observed validation use; adopt or remove after audit |
| No overlap found | forms, dates, charts, UI kits, HTTP | no duplicate library families detected |
| Potentially review | ESLint 8 / TypeScript 5.3 / Vite 5 / Orval 6 | versions are not current by 2026, but upgrade safety was not evaluated and is outside audit scope |

## 25. Performance Architecture

Vite manually splits `vendor` and the generated API surface, which prevents generated code from being mixed into the main app chunk. React Query cache settings and request cancellation support are helpful. There is no route-level `lazy`, `Suspense` code splitting, image pipeline, list virtualization, or explicit bundle analysis. All routes/pages are imported eagerly in `router.tsx`; the generated API bundle may be sizable despite its manual chunk. Large `per_page: 100` option fetches in creation/lookup forms are appropriate only while data volumes remain small. No polling/WebSockets eliminate one common rerender concern.

Potential risks: large admin/page modules, dashboard tables with unbounded rows returned from reports, `JSON.stringify` fallback display in generic collections, and broad query invalidation. The code uses native browser image/data URL patterns minimally; no image optimization case exists.

**Performance Architecture: 6/10.** A rebuild should add route/feature code splitting, measure actual chunks, retain Query caching, and choose virtualization only for proven large result sets.

## 26. Business Logic vs Presentation Logic

### A — UI can be replaced while logic stays

- Orval clients/models, HTTP transport, envelope/error normalization, Query client, CSRF/recovery (`api/**`).
- Staff/portal session semantics and permission constants/guards (`auth/**`, `portal/auth/**`).
- Ticket/customer adapters, mutations, bilingual helpers, list-query hooks, queue/task/reply/notification hooks (`features/*/api`, `features/*/use*.ts`).
- Report wire/filter descriptors and attachment upload integration.

### B — Some extraction required

- `CollectionPage.tsx` and `ResourceFormDialog.tsx`: reusable CRUD behavior is coupled to specific controls, string copy and raw records.
- Ticket/customer page compositions: panels are reusable but layout and view behavior are intertwined.
- Admin organization/security components: backend operations are clear but native controls/mutations live together.
- Workspace panels: query logic can move to view models/hooks.

### C — UI and business logic deeply coupled

- `pages/operations/OperationsPages.tsx`, `CreationPages.tsx`, `pages/admin/ChannelsPage.tsx`, `pages/public/PublicChannelPages.tsx`, and portal ticket detail bundle endpoint calls, state, validation, navigation, and markup. They need deliberate extraction during migration, not copy/paste.

**Business Logic Reusability Score: 7/10.** The backend-facing logic is unusually salvageable for a UI rewrite; only a minority needs extraction first.

## 27. Feature Inventory

| Module | Pages | Complexity | API dependency | UI complexity | Logic reusability |
| --- | ---: | --- | --- | --- | --- |
| Staff auth/account/2FA | 5 | Medium | High | Medium | High |
| Workspace/queues/tasks/notifications | 1 + panels | High | High | Medium | High |
| Tickets | 3 + detail/list features | High | High | High | High |
| Customers | 3 + detail/duplicates | High | High | High | High |
| Knowledge | 4 + lifecycle/categories | Medium-high | High | Medium | Medium-high |
| Reporting/dashboard/schedules | 4 | Medium | High | Medium | High |
| Organization | 6 + calendar features | High | High | Medium | Medium-high |
| Security/users/roles/settings/audit | 6 + panels | High | High | Medium | Medium-high |
| Ticket catalog/SLA/automation/channels | 4 | High | High | High | Medium |
| Integrations/AI/data protection | 3 | Medium-high | High | Medium | Medium-high |
| Customer portal | 10 | High | High | Medium | Medium-high |
| Public forms/chat | 3 | Medium-high | High | Medium | Medium |

## 28. Reusable Assets if We Rebuild

### Definitely reuse

- OpenAPI contract, Orval config/generated clients/models, `api/http/*`, query defaults and envelope/errors.
- Staff and portal auth contracts, permission keys/scopes, guard semantics, route authorization rules.
- Feature query/mutation hooks, adapters/wire transformations, bilingual/date/value utilities, attachment upload behavior.
- English/Arabic translation content as a migration source, with gaps filled rather than copied blindly.
- Existing unit test cases that encode business rules and current E2E route coverage.

### Reuse after cleanup

- `NAVIGATION` metadata and `useVisibleNavigation` permission filtering.
- Shared UI component behavioral contracts, `AsyncBoundary`, state components, and collection/form abstractions.
- Page route map, report descriptors/filters, saved view serialization, public/portal/session conventions.
- Text brand name and Plus Jakarta Sans only; they are not a full identity.

### Better to rewrite

- Staff and portal layout implementation, styling classes, generic icon approach, and visual component surfaces.
- Most tables, local pagination toolbars, direct Tailwind forms, card patterns, and raw dropdown/menu implementation.
- Hard-coded Arabic label/title maps and scattered hard-coded user text.
- Dead `DashboardPage.tsx` rather than migrating it.

## 29. Refactor Scenario

A serious refactor would first establish a token/component system, then progressively replace the shared shell, auth surfaces, tables, forms, dashboard and all feature layouts while maintaining both direct and generated API patterns during transition. It would need a coordinated migration away from raw Tailwind UI, a form strategy, table strategy, navigation/portal alignment, i18n cleanup, and visual/a11y regression testing. In-place refactoring protects current routes but creates a lengthy mixed-design period and increases CSS/component regression risk.

- Existing code reusable: **~60%** (mainly API/auth/hooks/types/tests)
- Existing code requiring modification: **~25%** (feature composition, adapters, metadata, generic CRUD)
- Existing code effectively replaced: **~15%** (current shell/primitives/styles and inactive legacy presentation)

These percentages are by engineering value, not line count; generated code inflates raw lines substantially.

## 30. Rebuild Scenario

A frontend rebuild should preserve API contracts, generated clients, HTTP/session/CSRF behavior, Query setup, permissions, feature adapters, translations, business-focused tests, assets/name, and routing/authorization knowledge. It would recreate app and portal shells, design tokens/primitives, navigation presentation, auth screens, forms presentation, tables/data views, dashboard widgets, responsive rules, error/empty/loading designs, and perhaps a cleaner feature-route architecture.

The biggest requirement is a compatibility migration: each new page must retain the same backend endpoint, permission guard, validation/error mapping, loading/empty/forbidden state, and deep link before its old UI is retired. A rebuild is viable because business logic already sits mostly outside the view markup.

## 31. Refactor vs Rebuild Decision Matrix

| Criterion | Refactor | Rebuild |
| --- | ---: | ---: |
| Preserve business logic | 9 | 8 |
| Preserve API integration | 9 | 8 |
| Design freedom | 5 | 10 |
| Technical debt removal | 5 | 9 |
| Migration risk | 7 | 6 |
| Regression risk | 7 | 6 |
| Long-term maintainability | 6 | 9 |
| Development complexity | 6 | 7 |
| Ability to create premium UI | 5 | 10 |
| Architecture cleanliness | 6 | 9 |
| Future scalability | 6 | 9 |

Scores favor a deliberately staged rebuild, not a big-bang replacement.

## 32. Final Recommendation

### C. Hybrid rebuild

Create a new frontend shell and design system while migrating reusable business/API logic from the current project.

This repository is not a weak prototype: typed contract generation, normalized transport, cookie/CSRF staff auth, TanStack Query, granular permissions, feature hooks/adapters, tests, and broad backend coverage are substantial assets. Rebuilding these would add avoidable risk. Conversely, the UI is only partially systematized, has two visual implementations, duplicated raw form/table patterns, incomplete localization, minimal branding, and no chart/interaction system. A premium redesign would repeatedly fight the existing presentation layer if treated as a cosmetic refactor.

## 33. Migration Strategy Recommendation

### Phase A — Preserve and harden infrastructure

- Keep API generation, mutator/envelopes, Query, auth/portal sessions, permissions, types, tests, and translations.
- Resolve the session-expiry signalling discrepancy before or as part of migration validation.
- Define one typed domain-service boundary for remaining handwritten endpoints.

### Phase B — New shared foundation

- Build tokens, themes, typography, icon policy, accessible primitives, state feedback, form patterns, table/list patterns, and i18n/RTL rules.
- Add visual/a11y responsive test coverage at the design-system level.

### Phase C — Shells and identity

- Replace staff/portal shells, navigation, menus, login/recovery, branding and responsive behavior while preserving route/permission behavior.

### Phase D — Highest-value work surfaces

- Migrate workspace, tickets, customers, attachments, and notifications using existing query/mutation logic.
- Implement the unified data-grid/list/form architecture before lower-risk CRUD modules.

### Phase E — Administration, reporting, knowledge, portal and public channels

- Move module by module behind route-compatible boundaries.
- Replace generic CRUD presentation only after each domain’s validation/permissions/actions are captured in tests.

### Phase F — Retire legacy presentation

- Remove duplicate UI paths only after equivalent live behavior, mobile/RTL, empty/loading/error states, and deep links are verified.

## 34. Final Scorecard

| Category | Score /10 |
| --- | ---: |
| Architecture | 7 |
| Code Quality | 6 |
| API Layer | 8 |
| State Management | 7 |
| Business Logic Reusability | 7 |
| Component Reusability | 5 |
| Design System | 5 |
| Styling Architecture | 5 |
| Responsiveness | 6 |
| RTL/LTR | 6 |
| Accessibility | 6 |
| Performance | 6 |
| Branding | 3 |
| Animation / Interaction | 4 |
| Maintainability | 6 |

**Overall Frontend Engineering Score: 6.2/10**  
**UI Foundation Score: 4.8/10**  
**Rebuild Necessity Score: 8/10**

## DECISION SUMMARY

The strongest assets are the integration and domain foundations: OpenAPI-driven TypeScript clients, centralized Axios/CSRF/envelope/error handling, TanStack Query, cookie-based staff authentication, separate portal authentication, granular permission constants and guards, meaningful feature hooks/adapters, bilingual resources, and a useful test base. These should be migrated, not recreated.

The fundamental weakness is the presentation architecture. A small shared UI layer and token file exist, but more than a hundred components still author direct Tailwind layouts and many tables/forms/buttons independently. Staff and portal behave as separate visual products; the app has minimal branding, no chart system, no icon system, no unified data-grid/form approach, and incomplete localization. The active dashboard is a basic report totals/table view, while an old unused dashboard remains in source.

What blocks a premium redesign is not backend/API quality; it is the mixed UI system. An in-place visual refactor would require modifying almost every screen while preserving a long-lived mix of old and new primitives. It also leaves existing concerns around mobile data tables, hard-coded strings, and component-level coupling.

Estimated reusable logic is **~60–70%** by engineering value, including API/auth/query/permissions/domain hooks and transformations. Estimated reusable UI is **~20–30%**, principally behavioral accessibility primitives and shared state boundaries rather than visual implementation. Biggest risks are changing auth/session behavior, missing a permission gate or mutation invalidation, portal token handling, and silently regressing domain-rich ticket/customer/admin actions during migration.

The recommended approach is a **hybrid rebuild**: retain the current backend contracts and portable domain logic, establish a clean new shell/design system, then migrate feature modules incrementally behind existing route and permission contracts. This yields premium design freedom and removes UI debt without a needless reimplementation of the working integration layer.

`Recommended Strategy: Hybrid rebuild`
