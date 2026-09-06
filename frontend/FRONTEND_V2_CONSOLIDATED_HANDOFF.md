# Frontend V2 — Consolidated Handoff

## Current State

Frontend V2 has completed the Design System, Auth, Staff Shell, Workspace, Tickets List, Ticket Workbench, and Customer surfaces; all gates through 4G are PASS, and the next migration surface is pending explicit selection.

**Current active gate:** NONE — Gate 4G closed PASS; next migration surface pending explicit selection.

Signal Ledger 1.1 remains binding. Do not reopen a completed gate without a specific regression or explicit request. Do not restart architecture or design-direction work, and do not select or begin a next migration surface implicitly.

## Working Rules

- Current frontend working directory: `/home/ibrahim/Desktop/deployment-configuration/www/competition-crm/frontend`.
- Application paths are relative to this directory (`src/...`); repository root is `..`.
- Preserve generated clients, Axios/mutator layers, TanStack Query keys, adapters, domain models, mutations, permissions, routing, CSRF/session behavior, and backend semantics.
- V2 presentation uses CSS Modules and semantic `--ds-*` tokens. No raw endpoint literals or transport calls in V2 JSX; no feature-level raw Radix imports.
- V2 pages use route-local `V2PortalBoundary`; do not place the whole authenticated outlet under a V2 boundary. Unmigrated sibling routes must stay visually isolated.
- Use the existing project tooling only: `../.agents/skills/impeccable/SKILL.md` and `../.agents/skills/ui-ux-pro-max/SKILL.md`. Do not install or initialize either tool.

## Repository and Checkpoint Context

- Frontend root: `/home/ibrahim/Desktop/deployment-configuration/www/competition-crm/frontend`; repository root: `..`.
- Most recent repository checkpoint visible when this handoff was refreshed: `31f14ab chore: checkpoint frontend work before Gate 4C`; historical checkpoints include `2a725eb feat(frontend): checkpoint V2 through authentication gate` and `868766d general fixes`.
- Git state is not assumed clean. Before any future implementation, inspect non-destructively with `git status --short`, `git log -3 --oneline`, and `git diff --stat`. Preserve unrelated user work; never reset, checkout, or clean it as part of a migration task.

## Phase 0 — BLOCKER-001 Session Expiry

BLOCKER-001 is resolved and is architectural, not ordinary UI work. Its canonical staff-session expiry flow is:

`staff request → eligible 401 → recovery attempt → recovery failure → expireSession() once/session → AuthProvider listener → clear auth/query state → unauthenticated → /login?reason=session_expired`

- `src/auth/session.ts` owns the canonical `expireSession()` single-fire lifecycle.
- `src/api/http/mutator.ts` identifies eligible staff 401s and invokes recovery/expiry behavior.
- `src/auth/AuthProvider.tsx` listens, clears state, and performs the unauthenticated redirect.
- Session and AuthProvider tests cover the event lifecycle; see `V2_PHASE0_BLOCKER001_REPORT.md`.
- Portal requests remain isolated from this staff-session flow. Do not reopen BLOCKER-001 without a proven regression.

## Hybrid Rebuild Strategy

The migration is a **Hybrid Rebuild**. It is not a full frontend rewrite, a cosmetic-only refactor, a new `/v2/*` application, or a backend/API redesign.

Preserve: React/TypeScript/Vite, React Router, TanStack Query, Axios/generated API/Orval, CSRF, auth/session behavior, permissions, API contracts, adapters/domain logic, existing routes and translations, E2E infrastructure, backend semantics, and portal isolation.

Replace progressively: legacy presentation, dashboard/card composition, V1 staff shell, and route-level V1 UI only when the relevant gate explicitly migrates that route. A route becomes V2 only when its gate explicitly migrates it; never move the complete authenticated `<Outlet/>` under `[data-ui="v2"]`.

## Architecture Binding

```text
OpenAPI
  ↓
generated API
  ↓
transport / mutator
  ↓
adapter / domain layer
  ↓
query / mutation / model
  ↓
feature UI
  ↓
route
  ↓
shell
```

- No endpoint literals, direct generated-client calls where a domain layer exists, or raw Axios/API calls in V2 presentation JSX.
- `src/design-system/**` must never import feature, API, auth, or business logic.
- Raw Radix imports are restricted to design-system wrappers.
- V2 visual styling uses CSS Modules and semantic tokens; existing Tailwind may remain structural only, never introduce raw V2 colors/radii/shadows.
- Lucide through the curated icon foundation is the V2 icon system.
- RTL uses logical properties, native Arabic layout, and intentional bidi handling rather than physical mirroring.

## Technology and ADR Context

Approved decisions and installed/security-attributed dependencies are recorded in:

- `V2_GATE2_TECH_DECISIONS.md`
- `V2_GATE2_DEPENDENCY_INSTALL_REPORT.md`
- `V2_GATE2_SECURITY_ATTRIBUTION.md`
- `docs/adr/006-ui-primitive-library-selection.md`
- `docs/adr/007-form-architecture.md`
- `docs/adr/008-data-table-architecture.md`
- `docs/adr/009-rtl-and-localization-architecture.md`
- `docs/adr/010-motion-policy.md`
- `docs/adr/011-staff-portal-shared-design-system.md`

In particular: Radix is wrapped, forms retain the approved existing architecture, a third-party data grid was deferred, RTL/localization is architecture rather than a CSS afterthought, motion is restrained, and the staff/portal design-system boundary is explicit.

## Signal Ledger 1.1 Direction

Signal Ledger is a restrained staff-operations system: mineral environment, open work planes, fine structural rules, IBM Plex Sans / IBM Plex Sans Arabic, sparse teal, semantic color families, limited radius/elevation, and text-led operational density. Avoid KPI-card mosaics, generic SaaS widgets, gradients, glass, glow, chart decoration, and colored badge proliferation.

Status, priority, and SLA remain separate concepts. Arabic is native RTL information architecture: use logical properties and bidi isolation for references, IDs, timestamps, email addresses, and URLs.

### Exact Approved Tokens and Binding

| Token | Value |
| --- | --- |
| Canvas | `#F6F7F5` |
| Base surface | `#FFFFFF` |
| Raised surface | `#FBFCFB` |
| Primary / secondary / muted text | `#16221F` / `#46534F` / `#65716B` |
| Subtle / strong border | `#DDE3DF` / `#B9C6BF` |
| Primary teal / hover / focus | `#006B5E` / `#00574D` / `#007D6E` |
| Restricted accent | `#8A531B` |
| Success / warning / danger / info | `#176B4A` / `#966100` / `#B42318` / `#1D5E91` |

Teal is scarce: it marks primary actions and deliberate focus, not every icon or widget. Typography is IBM Plex Sans and IBM Plex Sans Arabic. Latin scale: title `26/32`, narrow `22/28`, section `18/24`, body `14/20`, label/table `13/18`. Arabic optical scale: title `27/38`, narrow `23/34`, section `19/30`, body `15/24`, label `14/22`, table `14/22` or `15/24` when wrapping. Use tabular figures for operational data and isolate stable LTR values within Arabic UI.

Shape/depth: use 4/6/8px functional radii; remain flat by default; prefer hairlines over shadows; use shadows only for justified transient overlays. Dense desktop controls are 40px; touch-primary controls are at least 44px.

Motion: press about 80ms, hover about 120ms, menus/popovers 140–180ms, overlays 140–220ms, drawer 200–240ms. Do not animate sidebar width or add decorative page/list entrances. Honor `prefers-reduced-motion`. Review 1440, 1024, 768, and 375 widths.

### Never Reintroduce

- Dark enterprise sidebars for aesthetics, giant KPI-card grids, gradients, glass/frosted panels, or rounded-card-everything composition.
- Arbitrary status colors, color-only SLA semantics, giant marketing headings, generic shadcn/SaaS styling, or decorative charts.
- Hover-only critical actions, fake global search, fake command palettes, or AI that dominates the workflow.
- Dense staff UI copied directly into the customer portal.
- Raw Tailwind colors, radii, or shadows in V2 feature presentation.

## Gate Timeline

| Gate | Surface | Status | Confidence |
| --- | --- | --- | ---: |
| Phase 0 | BLOCKER-001 session expiry | RESOLVED | Architectural blocker closed |
| Gate 1 | Architecture blueprint | APPROVED | Approved |
| Gate 2 | Technology decisions | PASS | Approved |
| Gate 2.5 | Dependency install | PASS | Approved |
| Gate 2.6 | Security attribution | PASS | Approved |
| Gate 3A | Design direction | COMPLETE | Signal Ledger selected |
| Gate 3B | Impeccable design review | PASS | Signal Ledger 1.1 binding |
| 4A | Design System Foundation | PASS | Approved |
| 4A.5 | Visual QA | PASS | 9.2/10 |
| 4B | Authentication Experience | PASS | 9.3/10 |
| 4C | Staff Shell | PASS | 9.4/10 |
| 4D | Workspace V2 | PASS | 9.1/10 |
| 4E | Tickets List V2 | PASS | 9.1/10 |
| 4F | Ticket Workbench V2 | PASS — final validated state | 9.1/10 |
| 4G | Customer V2 | PASS — final validated state | 9.2/10 |

## Design System Foundation — Gate 4A / 4A.5

The V2 system lives under `src/design-system/`. Foundations include `foundations/V2PortalBoundary.tsx`, `tokens.css`, `typography.css`, `motion.css`, `v2-global.css`, curated Lucide icon foundation, and the foundation README where applicable. `V2PortalBoundary` exists because Radix portals otherwise escape the scoped V2 token environment; all V2 overlays must resolve through it.

Primitives: Button, IconButton, Input, Textarea, Label, Badge, Spinner, and VisuallyHidden. Composites: Dialog, AlertDialog, DropdownMenu, Popover, Tooltip, Tabs, and Drawer. State patterns: EmptyState, ErrorState, LoadingState, and ForbiddenState.

Gate 4A.5 visual QA passed at 1440 LTR/RTL and 1024/768/375, including keyboard/focus, overlays, reduced motion, bidi, and portal inheritance, at 9.2/10. Extend this foundation; do not create parallel feature-local design systems.

## Closed Gate Summaries — 4B Through 4E

### Gate 4B — Authentication Experience — PASS, 9.3/10

Migrated `/login`, `/login/two-factor`, `/forgot-password`, `/reset-password`, and `/invitations/:token` into the Ledger Frame. Existing login, 2FA, recovery, invitation, CSRF, and session semantics remain authoritative. Key report: `V2_GATE4B_AUTH_REPORT.md`.

### Gate 4C — Staff Shell — PASS, 9.4/10

Migrated StaffShell, StaffRail, StaffTopBar, navigation, account menu, and the real notification utility. Desktop retains a 248px rail; at `<=768` it becomes a labelled inline-start Drawer. There is no fake global search; notification behavior reuses existing real queries/mutations. The V2 shell is deliberately outside the normal route outlet boundary so unmigrated V1 content stays isolated. Key report: `V2_GATE4C_STAFF_SHELL_REPORT.md`.

### Gate 4D — Workspace V2 — PASS, 9.1/10

Migrated only Workspace (`/` and `/workspace`) through a route-local boundary. It answers operational questions with an attention band, My Work ledger, risk/exceptions, and recent changes—not a KPI-card mosaic. Queries, notifications relationship, permissions, actions, loading/empty/error states, Arabic, axe, and E2E were retained/validated. Key report: `V2_GATE4D_WORKSPACE_REPORT.md`.

### Gate 4E — Tickets List V2 — PASS, 9.1/10

Migrated `/tickets` into a desktop DataTable and mobile (`<=768`) ledger-style DataList. Existing search, filters, sort, pagination, selection, bulk behavior, permissions, and navigation are preserved. Do not fabricate SLA where list data lacks it. `/tickets/new` remains V1 route-modal behavior. Responsive, RTL, axe, and E2E passed. Key report: `V2_GATE4E_TICKETS_LIST_REPORT.md`.

## Closed Gate 4F — Ticket Workbench V2

**Final status:** `GATE 4F TICKET WORKBENCH: PASS`  
**Overall Ticket Workbench Confidence:** `9.1/10`

The canonical ticket-detail route is preserved and is now a route-local V2 surface through `V2PortalBoundary`. The Workbench keeps conversation and composer primary, with a cohesive secondary Signal Ledger inspector made from ruled open sections rather than legacy panel cards.

### Final Workbench Composition

- Compact identity/action band with stable, bidi-isolated ticket reference and subject-led hierarchy.
- Conversation-first reading ledger with requester messages, staff replies, explicit internal-note treatment, and quiet system/history context.
- Public reply/internal-note radiogroup, persistent mode safety warning, mode-specific submit wording, retained validation, mutation, conflict, loading, and secure attachment behavior.
- Cohesive inspector groups for State (status, priority, SLA), Assignment, Requester, Operational context (properties, watchers, tasks), History, and subordinate AI assistance.
- V2 status, priority, SLA, assignment, requester/customer, properties, watchers, task ledger, history/activity, AI, quick-reply, mention, and delivery/error/retry presentation.
- Existing APIs, queries, mutation hooks, permissions, query invalidation, routing, task/watcher behavior, upload security, and public/internal semantics remain unchanged.
- Sibling V1 routes remain protected; `/tickets/new` remains V1 and unchanged.

### Responsive and RTL Evidence

| Evidence | Result |
| --- | --- |
| 1440 English | PASS |
| 1440 Arabic seeded Workbench | PASS |
| 1024 English | PASS — no document overflow |
| 768 Arabic seeded Workbench | PASS |
| 375 English | PASS |
| 375 Arabic seeded Workbench | PASS |
| Long conversation | PASS — document owns scrolling; composer and inspector remain reachable |

The former invalid Arabic capture is resolved. Authenticated seeded-ticket checks at 1440, 768, and 375 prove the canonical detail route, visible subject, conversation, composer, inspector, and RTL boundary.

### Gate 4F Validation

| Check | Result |
| --- | --- |
| Typecheck | PASS |
| Lint | PASS |
| Build | PASS |
| Targeted Workbench tests | PASS |
| Workbench axe LTR | PASS |
| Workbench axe RTL | PASS |
| Workbench E2E | PASS |
| Tickets V2 → Workbench → back navigation | PASS |
| Conversation rendering | PASS |
| Responsive E2E (1440/1024/768/375) | PASS |
| RTL E2E (1440/768/375) | PASS |
| Full unit/integration suite | 162 passed; 2 accepted baseline failures |

Safe mutation evidence remains focused integration evidence because the authenticated browser seed is not a mutation-owned record: public reply, internal note, and metadata mutation semantics, loading, errors, and permissions are covered without mutating arbitrary staff data.

### Final Impeccable / UI UX Pro Max Closeout

| Severity | Prior finding | Final result |
| --- | --- | --- |
| P1 | Legacy inspector/card composition | Resolved through one route-local ruled Signal Ledger inspector. |
| P1 | Arabic capture did not prove loaded Workbench | Resolved through authenticated seeded Arabic Workbench validation at 1440/768/375. |
| P2 | Quick replies, mentions, delivery/retry used legacy presentation | Resolved with local V2 treatments preserving behavior. |
| P2 | Assignee was a UUID | Non-blocking documented limitation: ticket detail exposes no client-resolvable display identity; no human identity is fabricated. |

The final analytical review confirms conversation/composer dominance, inspector secondary hierarchy, safe public/internal distinction, responsive composition, and RTL/bidi handling. No unresolved P0 or P1 remains.

### Gate 4F File Inventory

Created:

- `src/pages/TicketDetailPageV2.module.css`
- `src/features/tickets/detail/TicketHeader.module.css`
- `src/features/tickets/detail/TicketConversation.module.css`
- `src/features/tickets/detail/TicketComposer.module.css`
- `src/features/tickets/detail/TicketInspectorV2.module.css`
- `e2e/ticket-workbench-v2.spec.ts`

Modified:

- `src/pages/TicketDetailPage.tsx`
- `src/features/tickets/detail/TicketHeader.tsx`
- `src/features/tickets/detail/TicketConversation.tsx`
- `src/features/tickets/detail/TicketComposer.tsx`
- `src/features/tickets/detail/TicketStatusControl.tsx`
- `src/features/tickets/detail/TicketAssignmentControl.tsx`
- `src/features/tickets/detail/TicketPropertiesPanel.tsx`
- `src/features/tickets/detail/TicketSlaPanel.tsx`
- `src/features/tickets/detail/CustomerContextPanel.tsx`
- `src/features/tickets/detail/TicketWatchersPanel.tsx`
- `src/features/tickets/detail/TicketTasksPanel.tsx`
- `src/features/tickets/detail/TicketHistoryPanel.tsx`
- `src/features/tickets/detail/TicketAiPanel.tsx`
- `src/pages/__tests__/TicketDetailPageV2.test.tsx`
- `e2e/ticket-workbench-v2.spec.ts`

Deleted: none.

## Closed Gate 4G — Customer V2

**Final status:** `GATE 4G CUSTOMER V2: PASS`  
**Overall Customer V2 Confidence:** `9.2/10`

`/customers` and `/customers/:customerId` are route-local V2 surfaces. `/customers/new` intentionally remains the existing V1 `RouteFormModal` and is a separate deferred surface.

### Final Customer Composition

- `/customers` is a Signal Ledger list with URL-backed search, status filter, server sort, pagination, semantic desktop table behavior, and labelled mobile ledger rows at `<=768`.
- `/customers/:customerId` is a route-local record: identity/status first, then notes, attachments, duplicate review, ERP context, timeline/history, and permission-gated block/unblock actions.
- Customer detail uses `CustomerRecordV2`, a Customer-local ruled Signal Ledger treatment. Generated API clients, adapters, query keys, URL state, mutations, permissions, CSRF/session behavior, route contracts, attachment semantics, merge/block behavior, and Ticket Workbench compatibility are preserved.

### Final Rendered Remediation

- **P1 resolved:** embedded V1 cards/default controls/action bars across contacts, notes, attachments, duplicates, ERP, timeline, and block/unblock were recomposed as Customer-local Signal Ledger presentation without global V1 restyling.
- **P2 resolved:** mobile ledger labels now occupy a separate logical block before their values.
- **P2 resolved:** the Arabic attachment selector is localized while retaining native upload behavior.
- **P2 resolved:** the extra/nested main landmark and unlabeled fallback upload input were corrected; populated axe is clean in LTR and RTL.

No unresolved P0/P1 or material P2 remains.

### Responsive, RTL, and Accessibility Evidence

| Evidence | Result |
| --- | --- |
| 1440 English | PASS |
| 1024 English with existing 248px Staff Rail | PASS |
| 768 / 375 responsive | PASS |
| Arabic real-data Customer list at 1440 / 768 / 375 | PASS |
| Arabic real-data Customer detail at 1440 / 768 / 375 | PASS |
| Document overflow at 1440 / 1024 / 768 / 375 | PASS — none |
| Populated Customer list axe English/LTR | PASS — zero violations in executed scope |
| Populated Customer detail axe English/LTR | PASS — zero violations in executed scope |
| Populated Customer list axe Arabic/RTL | PASS — zero violations in executed scope |
| Populated Customer detail axe Arabic/RTL | PASS — zero violations in executed scope |

Arabic uses logical layout, bidi isolation for stable LTR values, wrapping for long operational values, and localized attachment selection. The accessible real record had a Latin name and did not expose a real attachment filename, URL, or ERP identifier; those mixed-content payload cases were not fabricated. Injected axe disabled colour-contrast because production fonts were not consistently available in that environment; semantic-token and rendered-browser review provided supplemental contrast evidence.

### Gate 4G Validation

| Check | Result |
| --- | --- |
| Typecheck / lint | PASS |
| Focused Customer tests | PASS — 30/30 |
| Customer E2E | PASS — 5/5 |
| Production build | PASS |
| Axe LTR / RTL | PASS |
| Arabic rendered evidence | PASS |
| Impeccable rendered critique/audit | PASS |
| UI UX Pro Max final review | PASS |
| Full unit/integration suite | 162 passed; only 2 accepted admin wire baseline failures |

The only accepted baseline failures remain `src/features/admin/api/__tests__/wire.test.ts`: Branch name expected `"Cairo Branch"`, receives `""`; Department name expected `"Support"`, receives `""`.

### Gate 4G File Inventory

Created:

- `src/pages/CustomersPageV2.module.css`
- `src/pages/CustomerDetailPageV2.module.css`
- `src/features/customers/list/CustomerListTable.module.css`
- `src/features/customers/list/CustomerSearchBar.module.css`
- `src/features/customers/detail/CustomerRecordV2.module.css`
- `e2e/customers-v2.spec.ts`

Modified:

- `src/pages/CustomersPage.tsx`, `src/pages/CustomerDetailPage.tsx`
- `src/features/customers/list/CustomerListTable.tsx`, `src/features/customers/list/CustomerSearchBar.tsx`
- `src/features/customers/detail/CustomerHeader.tsx`, `CustomerIdentityPanel.tsx`, `CustomerNotesPanel.tsx`, `CustomerAttachmentsPanel.tsx`, `CustomerTimeline.tsx`, `ErpContextPanel.tsx`, `CustomerDangerActions.tsx`, and `CustomerStatusBadge.tsx`
- `src/features/customers/duplicates/DuplicateCandidatesPanel.tsx`
- `src/shared/attachments/AttachmentUploader.tsx`, `src/shared/confirm/ConfirmActionDialog.tsx`
- `src/i18n/en.json`, `src/i18n/ar.json`

Deleted: none.

### Customer Data Limitations

Current Customer APIs do not provide related tickets, a primary contact in list rows, company account/service tier, or customer-health metrics. None was fabricated; these are product/API limitations, not deferred UI requirements.

## Completed Gate Boundaries

- Gate 4B Auth is closed. Do not alter login, 2FA, recovery, invitation, AuthProvider, CSRF, or session-expiry behavior except for a proven regression.
- Gate 4C Staff Shell is closed. Keep the 248px desktop rail; at 768 it becomes a drawer. Do not redesign shell components. A future compact-rail mode is deferred unless real data density later proves it necessary.
- Gate 4D Workspace is closed. Its V2 page boundary is route-local; do not migrate unrelated routes through its scope.
- Gate 4E Tickets List is closed. `/tickets` remains V2; detail routes are now V2 through Gate 4F. `/tickets/new` remains its existing V1 route-modal flow.
- Gate 4F Ticket Workbench is closed. Do not alter its canonical detail route or Workbench composition except for a proven regression or explicitly authorized scoped integration.
- Gate 4G Customer V2 is closed. Do not alter `/customers` or `/customers/:customerId` except for a proven regression or explicitly authorized scoped cross-surface integration. `/customers/new` remains intentionally V1 and deferred.

## Accepted Baseline Failures

The only accepted full-suite failures are in `src/features/admin/api/__tests__/wire.test.ts`:

- Branch name expected `"Cairo Branch"`, receives `""`.
- Department name expected `"Support"`, receives `""`.

Do not fix or reclassify these in a future migration gate unless explicitly authorized. Any additional failure is a regression.

## Authoritative Report Index

- `FRONTEND_AUDIT.md` — original frontend inventory and gaps.
- `FRONTEND_V2_BLUEPRINT.md` — approved migration blueprint and boundaries.
- `V2_PHASE0_BLOCKER001_REPORT.md` — session-expiry resolution.
- `V2_GATE2_TECH_DECISIONS.md`, `V2_GATE2_DEPENDENCY_INSTALL_REPORT.md`, and `V2_GATE2_SECURITY_ATTRIBUTION.md` — approved technology/security context.
- `V2_GATE3_DESIGN_DIRECTION.md` and `V2_GATE3B_IMPECCABLE_REVIEW.md` — Signal Ledger direction and review.
- `V2_GATE4A_DESIGN_SYSTEM_REPORT.md` and `V2_GATE4A5_VISUAL_QA_REPORT.md` — V2 foundation and visual QA.
- `V2_GATE4B_AUTH_REPORT.md`, `V2_GATE4C_STAFF_SHELL_REPORT.md`, `V2_GATE4D_WORKSPACE_REPORT.md`, and `V2_GATE4E_TICKETS_LIST_REPORT.md` — closed-gate behavior and validation.
- `V2_GATE4F_TICKET_WORKBENCH_REPORT.md` — final authoritative Ticket Workbench closeout.
- `V2_GATE4G_CUSTOMER_REPORT.md` — final authoritative Customer V2 closeout.

## Deferred / Future Work

- Global search / command palette.
- Notifications transport work.
- Potential user-controlled compact shell at 1024 only if real future density evidence requires it.
- Tickets List SLA display limitation where list data does not expose real SLA.
- Knowledge V2.
- Admin V2.
- Portal V2.
- `/tickets/new` V1 route-form modal migration.

None of these is selected or authorized as the next migration gate.

## Start of Next Conversation

Continue the Frontend V2 migration using this consolidated handoff. Gates through 4G are closed PASS and Signal Ledger 1.1 remains binding. Do not reopen completed gates or restart architecture/design-direction work. No next migration surface is authorized: select it explicitly before implementation. Before future work, inspect Git state non-destructively.

GATE 4G CUSTOMER V2: PASS
