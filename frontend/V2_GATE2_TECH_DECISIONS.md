# Frontend V2 — Gate 2 Technology Decisions

## Scope and outcome

This review reads the approved `FRONTEND_AUDIT.md`, `FRONTEND_V2_BLUEPRINT.md`, and `V2_PHASE0_BLOCKER001_REPORT.md`, then verifies the current repository configuration. It approves a compatible dependency and enforcement plan only. No package, lockfile, runtime, UI, styling, route, API, or authentication change is made by this decision.

The approved V2 stack is compatible with the current locked baseline: React 18.3.1, React DOM 18.3.1, Vite 5.4.21, TypeScript 5.9.3, Vitest 1.6.1, ESLint 8.57.1, Tailwind 3.4.19, and Zod 3.22.4. `package.json` ranges are older than their current lockfile resolutions; this phase does not normalize or upgrade them.

## 1. Package compatibility

| Proposed addition | Current compatibility | Finding | Decision |
|---|---|---|---|
| Selective Radix primitives | PASS | Current Radix packages support React/React DOM 16.8 through 19. React 18.3.1 and TypeScript JSX/bundler resolution are compatible. | Install only the listed primitive packages. |
| `lucide-react` | PASS | Current Lucide supports React 16.5 through 19 and requires Node 18+; the project’s tooling targets Node 20 types. | Install for the single V2 icon source. |
| `react-hook-form` | PASS | React Hook Form 7 supports React 16.8 through 19. | Install when the forms POC begins. |
| `@hookform/resolvers` latest | FAIL without a Zod upgrade | Current resolver 5.9.1 peers with Zod `^3.25.0 || ^4.0.0`; the lockfile has Zod 3.22.4. | Pin compatible `@hookform/resolvers@3.10.0`; do not upgrade Zod in V2 foundation. |
| `vitest-axe` | PASS | `vitest-axe@0.1.0` peers with Vitest >=0.16 and includes axe-core. Current Vitest is 1.6.1. | Add as the smallest Vitest/Testing Library axe integration. |

Vite already supports CSS Modules for `*.module.css` natively. The current `@vitejs/plugin-react`, TypeScript settings, Vitest config, and `@` alias work with the proposed directory convention. No Vite plugin, TypeScript setting, PostCSS package, or test-runner replacement is needed.

The existing flat `eslint.config.js` is compatible with future narrow V2 rules/tests. It currently ignores generated code and does not enforce architecture. Add only targeted rules or source-scanning tests after V2 roots exist; do not introduce an architectural lint framework.

## 2. Exact Radix package plan

The first V2 design system, login/shell, workspace, and Tickets POC require the following minimum set. Feature code imports V2 wrappers, never these packages directly.

| Package | Required for POC? | Intended V2 component | Justification |
|---|---:|---|---|
| `@radix-ui/react-dialog` | Yes | `Dialog`, route-form modal | Existing route forms and ticket interaction need focus containment, Escape, portal layering, and restore-focus. |
| `@radix-ui/react-alert-dialog` | Yes | `AlertDialog`, confirm action | Destructive/bulk ticket actions need an explicit confirmation semantic. |
| `@radix-ui/react-dropdown-menu` | Yes | `DropdownMenu`, account/action menus | Replaces incomplete current `TopBar` menu behavior with robust keyboard/outside-dismiss handling. |
| `@radix-ui/react-popover` | Yes | `Popover`, contextual action/quick-reply surfaces | Ticket/workspace contextual content needs anchored, dismissible layers. |
| `@radix-ui/react-tooltip` | Yes | `Tooltip` | Supports labelled dense staff controls while icon-only controls still retain accessible labels. |
| `@radix-ui/react-tabs` | Yes | `Tabs` | Existing ticket/admin/workspace section patterns need accessible tab semantics. |

Deliberately deferred: `@radix-ui/react-select` (native `select` remains the default until an async/select-combobox need is designed), `@radix-ui/react-scroll-area` (native overflow is sufficient for POC), and every other Radix primitive. Add each only through a component ADR/need, not pre-emptively.

## 3. Lucide plan

Install `lucide-react` as the only general-purpose V2 icon source. V2 exposes a curated typed registry from `src/design-system/foundations/icons.ts`; it imports only named icons used by approved wrappers and does not re-export the entire Lucide package. Feature code imports named project icon aliases or passes approved icon components to design-system controls.

Standards:

- Default icon sizes: 16 px inline/dense, 20 px controls/navigation, 24 px prominent standalone. Exceptions require component variants, not arbitrary feature sizes.
- Preserve Lucide’s standard `currentColor`, rounded linecap/linejoin, and a token-backed default stroke width. Do not override individual SVG paths in feature code.
- Decorative icons render `aria-hidden="true"` and are not announced. An icon-only button requires a translated `aria-label` supplied by its wrapper API; tooltip text supplements but never replaces the accessible name.
- Directional icons (back/forward arrows, chevrons, send/return where direction conveys movement) mirror under RTL through an approved wrapper/class. Semantic icons (user, ticket, attachment, status, bell, search, settings) do not mirror.
- No text glyphs (`⌄`, `×`, `⏳`) or ad hoc inline SVG collections in V2 code. Brand marks are a later Gate 3 asset decision, not Lucide substitutes.

## 4. Form integration plan

Current `src/api/http/errors.ts` produces a normalized error with `message`, `fieldErrors`, `kind`, `code`, `status`, and request metadata. Existing forms already use field-error maps, so V2 keeps server validation authoritative and adds one domain-neutral bridge, proposed as `src/shared/forms/applyServerErrorsToForm.ts`.

```text
Zod schema → React Hook Form → V2 FormField controls → domain mutation
      ↑                                                    ↓
      └──── immediate structure/typed values    normaliseApiError(error)
                                                   ↓
                                      applyServerErrorsToForm(form, error)
```

Contract for the adapter:

| Server result | V2 form behavior |
|---|---|
| `fieldErrors[field]` | Call RHF `setError` with type `server`, preserving the backend message. |
| Nested fields, e.g. `name.en`, `targets.0.minutes` | Normalize only documented bracket/dot notation into RHF field paths; retain unknown paths as form-level errors rather than silently dropping them. |
| Form/global error | Store/show through a V2 `FormErrorSummary`; preserve server text and request ID where support diagnostics need it. |
| Unknown/error without field map | Use the normalized generic/server message as a form-level error; do not invent client validation. |
| Client Zod error | Present immediate translated schema message; a server response for the same field replaces/augments it as authoritative. |
| Backend status/value | Keep backend values as submitted; translated display labels belong to feature-domain maps, not the adapter. |

The adapter must not translate, rewrite, or discard server messages. Translation applies to client schema/UI framing; API messages are preserved because backend validation is authoritative. Ticket creation/composer and customer creation are the first appropriate proof cases. Existing controlled V1 forms are not converted during Gate 2.

## 5. CSS Modules compatibility

Vite 5 supports CSS Modules by filename convention; no package or configuration change is necessary. Use this structure for V2 design-system components:

```text
src/design-system/primitives/Button/
├── Button.tsx
├── Button.module.css
├── Button.test.tsx
└── index.ts
```

Global semantic variables later live in V2 foundation CSS (for example `src/styles/tokens.css`); module declarations consume `var(--...)` values. CSS Modules scope component selectors but do not scope CSS custom properties, document `dir`, media queries, or pseudo-classes. RTL uses logical properties by default and module selectors such as `:global([dir='rtl']) .root` only for a genuine direction-specific exception. Do not use physical left/right properties as a shortcut.

## 6. Tailwind role

Current `tailwind.config.js` has no theme extension and scans `src/**/*.{js,ts,jsx,tsx}`. Existing V1 files use raw palette/radius/shadow utilities extensively; that is not a V2 precedent.

### Tailwind allowed

Use it for local structural composition: `flex`, `grid`, approved-scale gaps, `min-w-0`, overflow behavior, container sizing, responsive grid placement, ordering, and layout-only visibility. Composition can remain colocated with a feature/page.

### Tailwind discouraged or forbidden in V2

Do not use it as visual identity or to recreate components: raw palette classes such as `bg-blue-600`/`text-slate-500`; arbitrary colors, radii, shadows, visual borders, z-indexes, animation values; feature-local button/input/table appearance; and handcrafted state styles that duplicate design-system APIs.

After Gate 3 token approval, a separate small configuration decision may expose approved semantic CSS variables through Tailwind utilities if that demonstrably improves layout composition. It must not convert Tailwind into the component styling source. No Tailwind configuration change is required now.

## 7. Accessibility testing

Proposed smallest package: `vitest-axe@0.1.0` as a dev dependency. It works with the existing Vitest jsdom environment and Testing Library render output, and includes axe-core; do not add Jest, Cypress, or another test runner.

Future component tests use the pattern below after installation:

```ts
import { axe, toHaveNoViolations } from 'vitest-axe';
import { render } from '@testing-library/react';

expect.extend({ toHaveNoViolations });
expect(await axe(render(<Component />).container)).toHaveNoViolations();
```

Add the matcher once in `src/test/setup.ts` when the package is installed. Axe is a regression gate for primitives and critical routes, not a replacement for keyboard tests, focus tests, translated-name assertions, manual screen-reader checks, responsive testing, or Arabic RTL coverage.

## 8. Architecture enforcement

Use lightweight, repository-native enforcement after V2 folders exist:

| Regression | Enforcement |
|---|---|
| V2 JSX calls `apiRequest`, Axios, or endpoint strings | A focused Vitest source-policy test scans designated V2 `ui/` and route files; allow only named `api/`, `auth/`, and test modules. Existing `src/api/README.md` remains the policy source. |
| Design-system imports features | Source-policy test scans `src/design-system/**` imports and rejects `@/features`, `@/portal`, route, auth permission, and API imports. |
| Raw Radix imports in feature UI | Source-policy test permits `@radix-ui/*` only under `src/design-system/**`. |
| Visible hardcoded V2 strings | Extend the existing `src/__tests__/no-hardcoded-strings.test.ts` with scoped V2 exceptions only for test IDs, technical values, and non-visible content. |
| Direct visual Tailwind palette/shape/shadow classes | Source-policy test on V2 roots rejects raw color/radius/shadow/border/animation utility families; code review verifies structural-only use. |
| Circular feature imports | Keep feature public APIs explicit and add a narrow import scan if an actual violation appears; do not add a monorepo dependency graph tool. |

The rules are intentionally scoped to V2 paths so they do not create a forced unrelated cleanup of V1. ESLint can later enforce straightforward restricted imports; source-policy tests are the least invasive way to cover path and class conventions with the current flat ESLint configuration.

## 9. Accepted ADRs

Documentation ADRs created with this decision:

- `docs/adr/006-ui-primitive-library-selection.md`
- `docs/adr/007-form-architecture.md`
- `docs/adr/008-data-table-architecture.md`
- `docs/adr/009-rtl-and-localization-architecture.md`
- `docs/adr/010-motion-policy.md`
- `docs/adr/011-staff-portal-shared-design-system.md`

## 10. Dependency installation plan

### Install before Design System

- `@radix-ui/react-dialog@1.1.23`
- `@radix-ui/react-alert-dialog@1.1.23`
- `@radix-ui/react-dropdown-menu@2.1.24`
- `@radix-ui/react-popover@1.1.23`
- `@radix-ui/react-tooltip@1.2.16`
- `@radix-ui/react-tabs@1.1.21`
- `lucide-react@1.39.0`
- dev dependency: `vitest-axe@0.1.0`

### Install when Forms POC begins

- `react-hook-form@7.87.0`
- `@hookform/resolvers@3.10.0` — pinned to avoid a Zod 3.25+ requirement; retain installed Zod 3.22.4.

### Defer

- TanStack Table — decide after the Tickets POC.
- Framer Motion/Motion — CSS-first motion remains approved.
- Recharts or another chart library — decide only with approved report/dashboard requirements.
- Radix Select, ScrollArea, and all unused Radix primitives — add only against a demonstrated component requirement.

## 11. Pre-existing test failures baseline

Retain the Phase 0 baseline note: the full Vitest suite has two unrelated failures in `src/features/admin/api/__tests__/wire.test.ts`. The affected mapper tests expect branch/department names but receive empty strings. They are not caused by BLOCKER-001 or Gate 2 work and must not be fixed as part of V2 technology adoption. Existing React `act` and React Router future-flag warnings are likewise baseline test noise, not V2 attribution.

## Exact next-task install commands

Run these only after the next implementation task explicitly authorizes dependency changes:

```bash
npm install @radix-ui/react-dialog@1.1.23 @radix-ui/react-alert-dialog@1.1.23 @radix-ui/react-dropdown-menu@2.1.24 @radix-ui/react-popover@1.1.23 @radix-ui/react-tooltip@1.2.16 @radix-ui/react-tabs@1.1.21 lucide-react@1.39.0
npm install --save-dev vitest-axe@0.1.0
```

Do not install React Hook Form/resolvers until a forms POC task is explicitly approved:

```bash
npm install react-hook-form@7.87.0 @hookform/resolvers@3.10.0
```

Gate 2 compatibility: PASS

Approved immediate dependencies:
- @radix-ui/react-dialog@1.1.23
- @radix-ui/react-alert-dialog@1.1.23
- @radix-ui/react-dropdown-menu@2.1.24
- @radix-ui/react-popover@1.1.23
- @radix-ui/react-tooltip@1.2.16
- @radix-ui/react-tabs@1.1.21
- lucide-react@1.39.0
- vitest-axe@0.1.0

Deferred dependencies:
- react-hook-form@7.87.0 and @hookform/resolvers@3.10.0 until Forms POC
- TanStack Table
- Framer Motion / Motion
- Recharts
- Additional Radix primitives

React upgrade required: NO
Vite upgrade required: NO
TypeScript upgrade required: NO

Ready for Gate 3 — Brand & Design Direction: YES
