# Frontend V2 — Gate 4C Staff Shell Report

## Existing Shell Inventory

| Area | Existing Implementation | Reused | Replaced |
| ---- | ----------------------- | ------ | -------- |
| Layout composition | `src/shell/AppLayout.tsx` (flex shell, mobile scrim, V1 classes) | Behavior (skip link, route-change focus, `#main-content` target) | Presentation — replaced by `src/app/shell/StaffShell.tsx` |
| Sidebar | `src/shell/Sidebar.tsx` (dark navy `<details>` accordion) | Navigation data model | Presentation — replaced by `src/app/shell/StaffRail.tsx` + `NavList.tsx` |
| Topbar | `src/shell/TopBar.tsx` (Tailwind-styled locale/notifications/account) | Locale toggle logic, `useAuth().logout` call | Presentation — replaced by `src/app/shell/StaffTopBar.tsx` |
| Navigation metadata | `src/shell/navigation.ts` (`NAVIGATION` tree with `id/labelKey/path/icon/permission/anyPermission/children`) | **Reused as-is**, unmodified | — |
| Permission filtering | `src/shell/useVisibleNavigation.ts` (`filterNavTree` over `usePermissions()`) | **Reused as-is**, unmodified | — |
| Permission decisions | `src/auth/usePermissions.ts`, `src/auth/permissions.ts` | **Reused as-is** | — |
| Notifications data | `src/features/workspace/notifications/{useNotificationsQuery,useNotificationMutations,notificationTarget}.ts` | **Reused as-is** (query/mutation/routing logic unchanged) | — |
| Notifications presentation | `src/features/workspace/notifications/{NotificationBell,NotificationMenu}.tsx` (Tailwind `bg-white`/`text-blue-600`) | — | Replaced by `src/app/shell/NotificationsMenu.tsx` (V2 Popover); old files deleted as orphaned (only importer was the deleted `TopBar.tsx`) |
| Async/empty/loading states | `src/shell/AsyncBoundary.tsx`, `src/shell/states/*` | **Reused as-is** for the loading/error/empty branching behavior; `NotificationsMenu` passes compact, popover-scaled V2 elements (a `Spinner`-led paragraph, not the shared page-region `LoadingState`/`EmptyState` patterns, per an Impeccable P3 finding) as override props, so nothing V1-styled ever renders inside V2-scoped overlays | Not touched |
| Route-level error/403/404 | `src/shell/RouteErrorBoundary.tsx`, `states/{ForbiddenState,NotFoundState}` | **Reused as-is** | — |
| Route-level create/edit modal | `src/shell/RouteFormModal.tsx` (V1 `Dialog` from `components/ui`) | **Reused as-is**, not a shell-chrome concern | — |
| Action-level permission gate | `src/shell/ActionGuard.tsx` | **Reused as-is** (not shell chrome, feature-level) | — |
| V2 portal/token boundary | `src/design-system/foundations/V2PortalBoundary.tsx` (Gate 4A) | Reused; extended with an additive, optional `className` prop | — |
| Radix Dialog/DropdownMenu/Popover wrappers | `src/design-system/composites/*` (Gate 4A) | Reused for account menu (DropdownMenu) and notifications (Popover) | — |
| Drawer/Sheet composite | Did not exist (Gate 4A explicitly deferred it) | — | **New**: `src/design-system/composites/Drawer/index.tsx`, built on the same Radix Dialog primitive as the existing `Dialog` composite |

## Files Created

- `src/app/shell/StaffShell.tsx`, `StaffShell.module.css`
- `src/app/shell/StaffRail.tsx`, `StaffRail.module.css`
- `src/app/shell/StaffTopBar.tsx`, `StaffTopBar.module.css`
- `src/app/shell/NavList.tsx`, `NavList.module.css`
- `src/app/shell/AccountMenu.tsx`, `AccountMenu.module.css`
- `src/app/shell/NotificationsMenu.tsx`, `NotificationsMenu.module.css`
- `src/app/shell/navigation.helpers.ts` (pure active-route matching functions)
- `src/app/shell/usePageContext.ts` (masthead page-context/breadcrumb derivation)
- `src/app/shell/__tests__/StaffShell.test.tsx` (15 tests)
- `src/app/shell/__tests__/navigation.helpers.test.ts` (9 tests)
- `src/design-system/composites/Drawer/index.tsx`

## Files Modified

- `src/router.tsx` — swapped `AppLayout` import/usage for `StaffShell`. No route paths, guards, or permission checks changed.
- `src/design-system/foundations/V2PortalBoundary.tsx` — added an optional `className` prop (additive; all four pre-existing call sites unaffected) so the rail/topbar boundaries can size correctly as flex items without an extra wrapper div.
- `src/design-system/foundations/icons.ts` — extended the curated Lucide icon set with the icons the shell renders (`Home, Inbox, Users, BookOpen, BarChart3, LayoutDashboard, ShieldCheck, LogOut, UserRound, Globe, Menu`).
- `src/design-system/composites/Overlay.module.css` — added `.drawerContent`/`.drawerTitle` styles and enter-animation keyframes (LTR/RTL variants) for the new Drawer composite.
- `src/i18n/en.json`, `src/i18n/ar.json` — added 11 new `shell.*` keys (skip link, nav landmark label, menu open/close, drawer title, breadcrumb landmark label, account trigger/link, locale switch labels, notifications loading). No existing keys changed; reused `nav.logout`, `workspace.notifications.*`, and existing `nav.*`/`reports.navigation.*` labels where already present.

## Files Deleted

- `src/shell/AppLayout.tsx`, `src/shell/Sidebar.tsx`, `src/shell/TopBar.tsx` — fully superseded by `src/app/shell/**`; confirmed via repo-wide grep that `router.tsx` was the only importer of `AppLayout`, which was the only importer of `Sidebar`/`TopBar`.
- `src/features/workspace/notifications/NotificationBell.tsx`, `NotificationMenu.tsx` — V1 presentation components, superseded by `NotificationsMenu.tsx`. Confirmed orphaned (only importer was the deleted `TopBar.tsx`) before deleting; the model layer they depended on (`useNotificationsQuery`, `useNotificationMutations`, `notificationTarget`, `wire.ts`) was left untouched.

## Unrelated Dirty Files Preserved

The working tree was clean at the start of this gate (only `tsconfig.tsbuildinfo`, a build artifact, was modified — no unrelated source changes existed). None of the previously-flagged unrelated areas (`RouteFormModal.tsx`, legacy modal work, V1 admin bilingual forms, V1 `index.css` changes) were dirty, so nothing needed to be preserved-around; `RouteFormModal.tsx`, `index.css`, and `PortalLayout.tsx` were left completely untouched by this gate.

## Shell Architecture

**Route integration.** `router.tsx`'s single authenticated parent route (`path: '/'`, wrapped in `ProtectedRoute`) now renders `StaffShell` instead of `AppLayout`. No route paths, `index`/nested structure, or per-route `ProtectedRoute`/`RequirePermission` guards changed — every staff URL is identical to before.

**V2 chrome boundary vs. V1 content isolation.** This was the central architectural constraint of the gate. `src/design-system/foundations/v2-global.css` scopes rules under `[data-ui="v2"]`, including a **universal descendant selector** (`[data-ui='v2'] *` for `box-sizing`, and `[data-ui='v2'] :is(button, input, textarea) { font: inherit }` in `typography.css`) and an inherited `color`/`background`. Any element that is a *descendant* of a `[data-ui="v2"]` node picks these up — including native, unstyled elements. Wrapping the whole shell (rail + topbar + `<Outlet/>`) in one `V2PortalBoundary` would therefore have silently re-themed every still-V1 page.

The fix: `StaffShell.tsx` renders **three independent `V2PortalBoundary` instances** — one for the skip link, one for the rail, one for the topbar — each a sibling of a plain, non-scoped `<main id="main-content">` that hosts `<Outlet/>`. `<Outlet/>` (and therefore all V1 route content) is never a descendant of any `[data-ui="v2"]` node. `StaffShell.module.css` documents this explicitly in a comment at the top of the file.

Layout uses a plain (non-V2) `display: flex` row for `[rail-boundary, column]`, with the column itself a `flex-direction: column` container for `[topbar-boundary, main]`. Flex `row` direction reads the document's inline direction automatically, so the whole shell mirrors correctly in RTL (rail moves to the visual right, topbar's control cluster moves to the visual left) with zero direction-conditional layout code — confirmed in browser QA (see below).

**Portal strategy.** Overlays (account `DropdownMenu`, notifications `Popover`, mobile `Drawer`) are declared inside the rail's or topbar's own JSX subtree, so `useV2PortalContainer()` resolves to that boundary's container via React context (not DOM position) — they render fully inside V2 token/font/`dir`/`lang` scope regardless of where Radix physically portals them in the DOM.

**Content-frame compatibility decision.** The `<main>`/inner `<div>` intentionally keep the **exact pre-existing V1 classes** `app-main`/`app-content` (from `src/index.css`, untouched). Two standalone V1 selectors — `.app-main .ui-card:has(> form)` and `.app-content > .grid` — style form cards and dashboard grids specifically when nested under `.app-main`/`.app-content`; dropping those class names would have been a silent visual regression for every V1 form/grid page. This is the one deliberate, documented compatibility touch called for in the gate brief (§4/§28); no other V1 selectors or files were touched.

## Navigation Model

`src/shell/navigation.ts`'s `NAVIGATION` tree (route/labelKey/icon/permission/anyPermission/children) was already exactly the centralized, typed metadata the blueprint calls for — reused unmodified. `src/app/shell/NavList.tsx` is the single rendering implementation shared by both the desktop rail (`StaffRail.tsx`) and the mobile drawer (declared inside `StaffTopBar.tsx`), so there is exactly one nav-rendering code path, not two.

**Active-route matching.** `navigation.helpers.ts` implements segment-boundary-aware matching (`pathname === path || pathname.startsWith(path + '/')`), plus one explicit rule: `router.tsx` renders the same `WorkspacePage` for both the index route (`/`) and the literal `/workspace` path, so the Workspace nav item is active on either. Nested routes (`/admin/branches/:branchId`, `/tickets/:ticketId`) correctly keep their parent list item active. **Note:** the implementation deliberately uses plain `Link`, not `NavLink`, for nav items — `NavLink` intercepts `aria-current`/`className` and computes them from its *own* internal active-matching (confirmed by reading `react-router-dom`'s source), silently discarding a caller-supplied active state. Since `navigation.helpers.ts` already computes the correct route-hierarchy-aware active state, `Link` with manually-set `aria-current`/className is the correct, non-fighting choice.

**Admin group disclosure.** The 12-item Administration group renders as a native `<details>/<summary>` (keyboard-native, no added JS framework, matches the pre-existing V1 pattern's affordance). It auto-expands when the current route matches one of its children (`hasActiveDescendant`) via a `useEffect`, and never auto-collapses a group the user left open. An empty-after-filtering group renders no heading at all (delegated entirely to `useVisibleNavigation`'s existing `filterNavTree`, which already drops a parent whose children all filtered out).

## Permission Handling

100% delegated to the existing `usePermissions()` → `hasPermission`/`hasAnyPermission` (`src/auth/permissions.ts`) via the unmodified `useVisibleNavigation()`. No new permission strings were introduced; no role-name comparisons exist anywhere in the shell (verified — `grep` for role literals in `src/app/shell/**` returns nothing, consistent with the repo's `no-role-names` architecture test). A forbidden route is never rendered disabled — it is absent from the DOM entirely (verified in tests via `queryByRole(..., { hidden: true })` returning nothing, not merely a hidden/disabled element).

## Desktop Rail

248px (`15.5rem`) fixed-width `V2PortalBoundary`-scoped `<aside>`, `position: sticky` pinned to the viewport top with its own `overflow-y: auto` (so a long nav list scrolls internally without disturbing document scroll), light `var(--ds-surface-base)` background against the mineral canvas, single `border-inline-end` rule (no shadow). Active item: text stays primary-weight, a `3px` `border-radius: full` `::before` bar on the logical inline-start edge (`inset-inline-start: 0`) — no pill, no filled block, no glow. Product identity is the existing `VITE_APP_NAME` string set in the same typographic treatment as the Gate 4B login `AuthFrame` identity block (no invented logo/mark).

## Top Utility

~64px (`min-height: 4rem`) sticky masthead, `var(--ds-surface-base)` with a single `border-block-end`. Contents (logical start → end): mobile-only drawer trigger, page-context label (with a two-level breadcrumb for admin sub-routes), then locale toggle / notifications / account menu. No fabricated regions — see Search/Notifications decisions below.

## Search Decision

**No existing global/command search was found anywhere in the inventoried codebase** (`shell/`, `features/`, `pages/`) — `common.search_records`/`common.search_collection` and the various `*.filters.search_placeholder` i18n keys are per-page list filters, not a shell-level omnibox. Per the gate's explicit instruction, **no search UI was added** to the shell. Documented as future work (see below) rather than fabricated.

## Notifications Decision

A real, working notifications feature already existed (`src/features/workspace/notifications/{useNotificationsQuery,useNotificationMutations,notificationTarget,wire}.ts`, backed by generated `GetNotifications`/`PostNotificationRead`/`PostNotificationsReadAll` API operations). It is **surfaced through new Signal Ledger shell chrome** (`NotificationsMenu.tsx`, a V2 `Popover`) using the exact same query/mutation/routing logic, unchanged. The old V1 presentation (`NotificationBell.tsx`, `NotificationMenu.tsx`) was deleted as now-orphaned.

## Account / Locale

**Account.** `AccountMenu.tsx` — a V2 `DropdownMenu` — shows initials-avatar + name (existing `AuthProvider.user`, no avatar image source exists so initials are used per the gate's explicit allowance), a `My account` link to the existing `/account` route (`StaffAccountPage`, unchanged), and `Logout`, which calls the existing `useAuth().logout()` — no auth logic touched. The trigger's accessible name is `"Account menu — {{name}}"` (i18n-interpolated) so it stays correct even where the visible name text is hidden at very narrow widths (`<480px`), rather than depending on visible text alone for its accessible name.

**Locale.** Reused the existing `useLocale()`/`i18n.changeLanguage` mechanism verbatim (same `localStorage` persistence, same `document.documentElement.lang`/`dir` side effects) — no second locale store. Presentation changed from a text toggle button to an icon-only `Globe` `IconButton` with an i18n-driven accessible label (`Switch to Arabic`/`Switch to English`), to keep it a fixed, touch-friendly size at every breakpoint instead of a variable-width text button.

## Responsive Strategy

### 1440 / Desktop

248px rail persists; topbar stays compact; content frame has no artificial max-width narrowing (`app-content`'s existing `min(100%, 100rem)` cap, unchanged) — verified in browser QA and the existing `e2e/responsive.spec.ts` desktop-width run.

### 1024

**Decision: Option A — the full rail persists.** At 1024px the 248px rail plus content still fits comfortably (verified in browser QA, screenshot below); no user-controlled compact/icon-only rail state was introduced in this gate, avoiding invented `localStorage` persistence for a preference the gate treats as optional. Documented here as the chosen behavior per the gate's explicit "choose the simplest robust behavior" instruction; a future gate may add a user-controlled compact toggle if data-dense screens at 1024 prove it's needed.

### 768

At `max-width: 768px` (`.railBoundary`, `.mobileOnly` breakpoints) the persistent rail hides entirely and the topbar's hamburger trigger becomes visible, opening the mobile drawer. Verified in browser QA in both English and Arabic.

### 375

Hamburger + truncating page-context label + icon-only locale/notification triggers + avatar-only account trigger (name text hidden `<480px`) fit without horizontal overflow — verified via `e2e/responsive.spec.ts`'s `expectNoDocumentOverflow` check across 375/768/1024/1440 (all pass) and via direct 375px screenshots (English and Arabic).

## Mobile Drawer Decision

Built `src/design-system/composites/Drawer/index.tsx` — the same underlying `@radix-ui/react-dialog` primitive already used by the existing `Dialog` composite (per the gate's "build from already-approved Radix/Dialog infrastructure" and "smaller maintainable solution" guidance), not a new dependency. It is a genuinely reusable design-system composite (`Drawer`/`DrawerTrigger`/`DrawerContent`/`DrawerClose`), not a shell-local one-off, satisfying "reusable by future V2 pages." Slides from the logical inline-start edge (`inset-inline-start: 0`, LTR/RTL-specific `translateX` keyframes selected via the same `[data-ui='v2'][dir='rtl']` attribute-selector pattern already used elsewhere in the foundation CSS), full-height, `min(85vw, 20rem)` wide. Focus trap, Escape-to-close, and focus-restoration-to-trigger come from Radix's `Dialog` primitive for free (verified in tests and consistent with the existing `Dialog` composite's proven test coverage). Selecting a nav item inside the drawer closes it (`onNavigate` callback clears the open state).

## RTL

Verified in real browser QA (not just source inspection) at 1440 and 375, both with the shell in Arabic:
- Rail renders on the logical/visual right; topbar's control cluster (locale/notifications/account) moves to the visual left; the hamburger+context label move to the visual right at narrow widths — all automatic from `flex-direction: row` respecting document `dir`, zero direction-conditional layout code.
- The `3px` active-item marker uses `inset-inline-start`, correctly landing on the visual right edge in RTL.
- Mobile drawer opens from the visual right (logical inline-start) in RTL.
- Directional breadcrumb chevron (`ChevronRight` + the foundation's existing `directionalIconClass`/`.ds-directional-icon` helper) mirrors; non-directional icons (nav icons, bell, globe, account) do not.
- Arabic labels wrap/render with the approved Arabic optical type scale (inherited automatically — `typography.css`'s `[lang='ar']` scale switch is unmodified).
- Email in the account menu is wrapped in the foundation's existing `.ds-bidi-value` isolation class to keep it LTR inside RTL surrounding text.

## Accessibility

- Semantic `<nav aria-label="Main navigation">` landmark (rail and drawer both use it — scoped by which is actually in the DOM/open at a given time).
- `<main id="main-content" tabIndex={-1}>` — the real main landmark; route-change effect (`document.getElementById('main-content')?.focus()`) preserved verbatim from the old `AppLayout`.
- Skip-to-content link, translated, visible on `:focus-visible`, targets `#main-content` — works in both LTR and RTL (logical `inset-inline-start`).
- `aria-current="page"` set correctly (via the custom active-state computation, not `NavLink`'s own) on the current top-level item, the current admin child, and correctly reflects the `/` ↔ `/workspace` alias.
- Icon-only controls (locale toggle, notifications bell, mobile menu trigger/close, drawer close) all have accessible names via `aria-label`/i18n.
- Account menu trigger has an accessible name that includes the user's actual name (not a generic "Account menu" that would mismatch the visible label — WCAG "Label in Name").
- Mobile drawer: labelled dialog (`Navigation`), Radix-managed focus trap, Escape-to-close, focus-restoration-to-trigger — all covered by an automated test. With the drawer open, Radix's default modal `aria-hidden`-isolation removes the desktop rail's landmark from the accessibility tree (verified by a dedicated test asserting exactly one "Main navigation" landmark remains, plus a clean axe run in that state) — so there is never a duplicate-landmark ambiguity for assistive tech, even though both `<nav>` elements are simultaneously present in the DOM.
- 44px (`2.75rem`) touch targets on every topbar/drawer control (hamburger, drawer close, locale toggle, notifications, account trigger) — raised from the initial 40px pass after an Impeccable P2 finding (see below); uses `IconButton`'s existing `touch` size class from Gate 4A rather than a new size scale.
- Targeted `vitest-axe` checks pass for the shell in both English and Arabic (see Validation table).

## V1 Isolation Verification

**Confirmed both in an automated test and in real browser QA.** `StaffShell.test.tsx`'s "places the rail and topbar inside the V2 boundary but keeps routed (V1) content outside it" test asserts `content.closest('[data-ui="v2"]')` is `null` for the `<Outlet/>`-rendered content while `nav.closest('[data-ui="v2"]')` is not. In the real browser (screenshots below), V1 page content (workspace cards, admin tables, the blue "Add task"/"Create" buttons) visibly retains its pre-existing V1 styling — no teal, no IBM Plex Sans, no V2 token bleed — while the new rail/topbar are fully Signal-Ledger-styled. `src/index.css` was not modified.

## UI UX Pro Max Findings

| Severity | Finding | Resolution |
| -------- | ------- | ---------- |
| — | Validated: rail persisting through 1024px (not collapsing to icon-only) matches the dataset's "large screens (≥1024px) prefer sidebar" adaptive-navigation guidance — no change needed. | No action (confirms existing decision) |
| — | Validated: breadcrumbs shown only for admin sub-routes (3-level depth) matches "use breadcrumbs for 3+ level hierarchies; don't use for flat pages" guidance. | No action (confirms existing decision) |
| — | Validated: all icon-only controls already carry `aria-label`, matching the dataset's ARIA-labels guidance. | No action (confirms existing decision) |

No material/actionable findings specific to the 12-item collapsible admin group or the icon-only topbar cluster were returned by the local guideline dataset beyond the general rules above, which the shell already satisfies.

## Impeccable Findings

Ran as a dual, isolated critique per the skill's protocol: Assessment A (design review — heuristics, cognitive load, RTL, specificity) and Assessment B (deterministic detector + independent live-browser evidence at 1440/375, LTR/RTL) each ran as a separate sub-agent with no visibility into the other, then synthesized here.

**Assessment B (detector + browser)**: `detect.mjs --json` against `src/app/shell` returned zero findings (16 files scanned); a manual grep confirmed zero raw hex/rgb colors anywhere in the shell's CSS modules — every declaration routes through `var(--ds-*)` tokens. Live screenshots (workspace, admin nested route, account menu, mobile 375, mobile drawer, Arabic desktop, Arabic mobile, Arabic drawer) were all clean — no clipping, no overflow, correct RTL mirroring confirmed against the actual `enterDrawerRtl` keyframe engaging. The one visually-arbitrary color it flagged (a blue "Add task"/"Create" button) was confirmed, by grep, to originate from out-of-scope V1 page content, not shell chrome.

**Assessment A (design review)**: scored the shell 9/10 heuristics on real signal (H10 "help and documentation" scored 0 — no help entry point anywhere, but that's the pre-existing, explicitly out-of-scope-for-this-gate absence of any help feature, not a shell defect) and surfaced five findings, all shell-scoped and fixed within this gate:

| Severity | Finding | Resolution |
| -------- | ------- | ---------- |
| P1 | Nav hover state (`--ds-surface-subtle` fill) visually outranked the actual active-page state (bar only, no fill) — hovering an adjacent item could look more "current" than the real current page. | **Fixed.** `.link.active`/`.groupSummaryActive`/`.childLink.active` now also fill with `var(--ds-surface-selected)`, the token already defined for exactly this purpose and already used by the menu-item highlight state. Verified live: current page now has an unambiguously stronger fill than a merely-hovered sibling. |
| P1 | `AccountMenu`'s Logout was an undifferentiated `DropdownMenuItem` directly under "My account" — no confirmation, no danger styling; one misclick ends a real support session. | **Fixed.** Logout now has a top divider and renders in `var(--ds-semantic-danger-fg)`, with its hover/highlight background locally overridden to the danger tone via a CSS-custom-property redefinition (robust regardless of stylesheet load order — see code comment). Verified live at rest and on hover. |
| P2 | The breadcrumb's parent segment (e.g. "Administration") had every visual affordance of a link (chevron separator, muted-vs-bold styling) but was inert, and the whole region had no `nav`/breadcrumb landmark for assistive tech. | **Fixed.** `usePageContext()` now returns `parentPath` (`/admin` — a real route, `AdminIndexPage`, that `NAVIGATION`'s `'#'`-placeholder group metadata doesn't itself carry); the parent renders as an actual `Link`, wrapped in `<nav aria-label="Breadcrumb">`, with `aria-current="page"` on the current segment. Verified live (clickable, navigates) and by a new automated test. |
| P2 | Every mobile-relevant control in the topbar/drawer (hamburger, drawer close, locale toggle, notification bell, account trigger) used 40px sizing, under the design system's own already-shipped 44px `touch` target size. | **Fixed.** Hamburger, drawer close, and locale `IconButton`s now pass `size="touch"`; `NotificationsMenu`/`AccountMenu`'s custom triggers bumped from `2.5rem` to `2.75rem` to match. |
| P3 | Notifications popover's empty/loading state used the shared `EmptyState`/`LoadingState` pattern's `mode="region"` styling (border, 24px block padding, section-scale heading) — visually heavier than its 22rem popover container warrants. | **Fixed.** Replaced with a compact inline `<p>` (still translated, still using `Spinner` for the loading case) sized for a popover rather than a full page region. |

Left standing, not fixed (explicitly out of this gate's scope per the brief, not a shell defect):
- **P3** No global search/command palette, keyboard shortcuts, or help entry point in the shell. The gate explicitly forbids fabricating search/command-palette UI where none exists; documented under "Future Integration Points."
- **P3** No user-controlled compact-rail toggle at 1024px. Documented as the deliberately-chosen "Option A" behavior (§1024 above) and as a future integration point.
- Brand wordmark stays Latin-script in Arabic — noted by Assessment A as a minor observation, not a defect; matches the same identity treatment already approved for the Gate 4B login `AuthFrame`, so changing it here alone would create an inconsistency rather than resolve one.

## Browser QA

| View                      | Result |
| ------------------------- | ------ |
| 1440 English              | PASS — 248px rail, compact topbar, correct hierarchy, active state, no accidental V2 restyling of V1 content |
| 1440 Arabic               | PASS — rail on visual right, correct reading order, active marker on visual right, account/menu alignment correct |
| 1024 English               | PASS — full rail retained, content still comfortable |
| 768 Arabic                 | PASS — drawer opens from visual right, topbar reduced correctly, no clipping |
| 375 English                | PASS — hamburger trigger, no horizontal overflow, touch targets, account reachable |
| 375 Arabic                 | PASS — Arabic labels wrap safely, drawer/topbar mirror correctly, no clipping |
| Account dropdown open      | PASS |
| Notifications popover open | PASS — heading, "Mark all as read", empty-state copy render correctly |
| Mobile drawer open         | PASS — labelled dialog, nav list, close control |
| Nested route active state  | PASS — `/admin/branches` keeps Administration expanded + Branches active + breadcrumb; `/tickets/new`'s existing `RouteFormModal` (V1 `Dialog`) also confirmed rendering correctly on top of the new shell chrome, with "Tickets" still active behind it |

Verified against the real Docker-backed API with a genuine authenticated staff session (Playwright storage state from `e2e/global-setup.ts`, not a mock), via the project's existing `e2e/route-smoke.spec.ts` (45/45 passed — every staff route renders through the new shell), `e2e/responsive.spec.ts` (8/8 passed, including the `expectNoDocumentOverflow` check at 375/768/1024/1440), and `e2e/core-workflows.spec.ts` (4/4 passed). Re-run in full a second time after applying the Impeccable-driven fixes below, with the same 57/57 result, plus targeted screenshots confirming each fix live (active-state fill now visibly outranks hover; Logout renders in danger red with a divider and a danger-tinted hover state; the "Administration" breadcrumb segment is a working link).

## Regression Verification

| Behavior                            | Result |
| ------------------------------------ | ------ |
| Login / Gate 4B unchanged            | PASS — no auth-page files touched |
| Auth/session architecture unchanged  | PASS — `AuthProvider`, `session.ts`, guards untouched; shell only consumes `useAuth()` |
| Staff routes unchanged               | PASS — `router.tsx` diff is exactly one import + one element swap; all 27 staff routes smoke-tested |
| Permission behavior unchanged        | PASS — `usePermissions`/`permissions.ts` untouched; navigation filtering delegates to unmodified `useVisibleNavigation` |
| Logout unchanged                     | PASS — `AccountMenu` calls the same `useAuth().logout()` |
| Locale behavior unchanged            | PASS — same `useLocale()`/`i18n.changeLanguage`/`localStorage` mechanism |
| V1 page content functional           | PASS — `route-smoke`/`core-workflows` create/update workflows pass end-to-end |
| Portal unaffected                    | PASS — `PortalLayout`/`portal/**` not touched; portal e2e routes pass |

## Validation

| Check       | Result |
| ----------- | ------ |
| Typecheck   | PASS — `npm run typecheck` |
| Lint        | PASS — `npm run lint` (`--max-warnings=0`) |
| Shell tests | PASS — 24 new tests (`StaffShell.test.tsx` ×15, `navigation.helpers.test.ts` ×9) |
| V2 tests    | PASS — existing design-system/auth V2 tests unaffected |
| Full tests  | PASS — 152 passed, 2 known baseline failures (below) |
| Build       | PASS — `npm run build` |
| Axe         | PASS — targeted shell axe checks, English and Arabic |
| E2E (real backend) | PASS — 45/45 route-smoke, 8/8 responsive, 4/4 core-workflows |

## Baseline Failures

`src/features/admin/api/__tests__/wire.test.ts` — two pre-existing, unrelated failures (Branch name expected `Cairo Branch`, Department name expected `Support`, both receive `''`). Not touched, not caused by this gate; explicitly listed as an acceptable baseline in the gate brief.

## Remaining Shell Risks

- The 1024px "full rail persists" decision has not been stress-tested against a genuinely data-dense future screen (e.g. a wide ticket table); a future gate may need the user-controlled compact-rail option this gate deliberately deferred.
- The Administration group's `<details>`/`<summary>` disclosure is native and accessible, but its expand/collapse state is session-only (component state, not persisted) — acceptable per the gate's "do not invent localStorage persistence casually" instruction, but worth revisiting if user feedback wants it remembered.
- Notifications popover reuses the existing 30s poll (`refetchInterval` in `useNotificationsQuery`) — unchanged, but worth knowing it's still polling-based, not push-based.

## Future Integration Points

Documented only, not implemented in this gate:
- **Workspace V2 / Tickets V2**: the content frame (`<main class="app-main">`) is ready to host them; no shell change needed when those gates land.
- **Global/command search**: no existing search to migrate; a future gate would need to design and build one from scratch (out of scope here).
- **Notifications**: currently polling-based; a push/real-time transport would be a backend-first change.
- **Route-context primary action** (e.g. a topbar "Create ticket" button on `/tickets`): explicitly omitted per the gate's "don't fabricate contextual actions" instruction; a future gate could add it once each feature route defines what its primary action is.
- **1024 compact-rail toggle**: documented above as a possible future addition, not built.

## Gate Assessment

| Area                           | /10 |
| ------------------------------- | --: |
| Signal Ledger fidelity          | 9.4 |
| Navigation clarity              | 9.5 |
| Information hierarchy           | 9.3 |
| Desktop long-session usability  | 9.3 |
| Responsive behavior             | 9.3 |
| Mobile navigation               | 9.3 |
| RTL                             | 9.4 |
| Accessibility                   | 9.4 |
| V1/V2 isolation                 | 9.5 |
| Permission correctness          | 9.4 |
| Premium perception              | 9.2 |
| Avoids generic SaaS             | 9.1 |

Overall Staff Shell Confidence: 9.4/10

GATE 4C STAFF SHELL: PASS
