# Frontend V2 — Gate 4A Design System Foundation Report

## Files Created

- `src/design-system/foundations/`: semantic Signal Ledger token, typography, motion, icon, global-isolation, and guidance files.
- `src/design-system/primitives/`: Button, IconButton, Input, Textarea, Label, Badge, Spinner, and VisuallyHidden.
- `src/design-system/composites/`: branded wrappers for Dialog, AlertDialog, DropdownMenu, Popover, Tooltip, and Tabs.
- `src/design-system/patterns/`: flexible Empty, Error, Loading, and Forbidden state patterns.
- `src/design-system/showcase/DesignSystemShowcase.tsx`: non-routed development visual harness.
- `src/design-system/__tests__/`: component accessibility/keyboard coverage and source-policy enforcement.

## Files Modified

- `src/main.tsx`: loads the isolated V2 foundation stylesheet only; no route or provider changed.
- `src/test/setup.ts`: loads `vitest-axe` matcher support for the existing Vitest environment.

## Token Summary

`[data-ui="v2"]` owns all V2 variables, preventing a V1 collision until a route is explicitly migrated. Tokens cover the approved mineral surfaces, text hierarchy, borders, teal brand signal, restricted accent, five semantic families with foreground/background/border roles, 4px spacing rhythm, 4/6/8px functional radii, two transient elevations, z-index layers, and semantic motion.

The Light palette is exactly Signal Ledger 1.1. `#8A531B` is exposed only as the restricted editorial accent; no primitive treats it as CTA, focus, or status. Components consume semantic variables rather than raw light values, keeping the API dark-ready without implementing a dark palette or toggle.

## Typography Summary

IBM Plex Sans and IBM Plex Sans Arabic were not installed locally or bundled in project assets. The foundation therefore uses a Google Fonts import, consistent with the existing application policy; no font binary was downloaded or added. Latin and Arabic have independent variable scales. Arabic receives larger body/label/table metrics and line heights, while numerical operational values use Latin tabular figures. `.ds-bidi-value` provides isolated LTR rendering for IDs, dates, emails, and URLs inside RTL content.

## Components Implemented

- Button: primary, secondary, ghost, danger; compact/default/touch; icon, loading, disabled, and stable text layout.
- IconButton: caller-required accessible name; 40px default/44px touch target; directional-icon support.
- Label, Input, Textarea: error, disabled, readonly, ARIA-invalid, description-compatible native semantics, and script-safe vertical metrics.
- Badge: neutral/info/success/warning/danger only; text-led and no backend mapping.
- Spinner and VisuallyHidden.
- Flexible state patterns with region/full modes, title, description, and caller-provided action—not a repeated centred illustration pattern.

## Radix Wrappers Implemented

Dialog, AlertDialog, DropdownMenu, Popover, Tooltip, and Tabs are the only V2 locations that import the approved Radix packages. The wrappers use quiet raised surfaces, precise borders, limited elevation, tokenized entry motion, and Radix keyboard/focus management. All labels and visible copy are supplied by the caller.

## Accessibility Coverage

- `vitest-axe` executes axe checks for buttons, IconButton, labelled input, Dialog, DropdownMenu, and Tabs.
- Tests also verify icon-button accessible naming, RTL direction setup, Dialog Escape/focus restoration, menu rendering, and tab selection semantics.
- The jsdom axe run disables `color-contrast` because jsdom cannot render computed contrast, and `region` because Radix portals intentionally render outside the test container. Production contrast is controlled by the reviewed token palette and still requires browser QA at the POC gate.

## RTL Coverage

All foundation CSS uses logical properties. The V2 boundary supports `dir="rtl"`/`lang="ar"`, switches to the Arabic optical scale, mirrors only the `.ds-directional-icon` helper, and leaves semantic icons unchanged. Showcase and component tests include Arabic/RTL examples; bidi isolation is documented and available as a dedicated utility.

## Architecture Enforcement

The V2 source-policy test rejects design-system imports from feature, API, and auth paths; rejects raw Radix imports outside `src/design-system`; rejects raw Tailwind palette/radius/shadow/animation utilities in V2 TypeScript/TSX; and rejects literal visible JSX copy in production primitives/composites. `src/design-system/foundations/README.md` records the matching operational rules, including no arbitrary visual values and no V2 feature imports.

## Visual Showcase

`DesignSystemShowcase` is an exported, non-routed development harness. It demonstrates typography, surfaces, LTR/RTL content, Button variants, inputs, badges, Dialog, DropdownMenu, Tabs, and state patterns. It creates no production route and is not imported by the application.

## Impeccable Critique Findings

- Dual independent Impeccable passes found a P0 portal-boundary defect: default Radix portals escaped the V2 token/font/RTL scope. This was corrected with `V2PortalBoundary`, an explicit V2 portal host used by every wrapper and asserted in the Dialog test.
- The passes also found and corrected document-level Arabic metric matching, RTL-safe physical dialog centring, semantic danger hover, distinct dropdown z-index use, and configurable state-pattern heading level.
- Detector scans before and after fixes returned `[]`. The source-only assessment cannot replace screenshot QA of a mounted development harness; that review remains required before a V2 route migration.

## Fixes Applied

- Scoped V2 global rules under `data-ui="v2"` after reviewing V1 global CSS, rather than changing V1 globals.
- Used the Gate 3B-muted `#65716B` and accessible restricted accent `#8A531B`; rejected Gate 3A’s unsafe normal-text values.
- Replaced fixed Latin-only metrics with script-aware variables and bidi isolation.
- Added semantic background/border token roles so badges and state patterns do not manufacture component-local colors.
- Added source-policy tests to prevent V2 architecture drift before route migration.
- Added `V2PortalBoundary` so portaled Dialog/AlertDialog/DropdownMenu/Popover/Tooltip content inherits V2 tokens, typography, language, direction, and focus treatment rather than falling back to V1/body styles.
- Replaced direction-sensitive logical dialog centring with documented physical centring, the necessary exception because CSS transforms remain physical in RTL.

## Validation

| Check | Result |
| --- | --- |
| Typecheck | PASS — `npm run typecheck` |
| Lint | PASS — `npm run lint` |
| Targeted tests | PASS — 8 V2 tests |
| Full tests | BASELINE ONLY — 124 passed, 2 known admin mapper failures |
| Build | PASS — `npm run build` |
| Axe | PASS — foundation coverage as documented above |

## Baseline failures

The full suite retains the two pre-existing, unrelated failures in `src/features/admin/api/__tests__/wire.test.ts`: Branch name expected `Cairo Branch` and Department name expected `Support`, both received empty strings. No V2 design-system code imports or modifies the related mapper. Existing React Router future-flag warnings also remain baseline noise. Radix test interaction emits React `act(...)` warnings in this React 18/jsdom combination; tests pass and no runtime behavior is altered, but this should be revisited when the test environment is next updated.

## Remaining Design-System Risks

- Token contrast and overlay positioning still need browser/screenshot verification when the showcase is mounted in a dedicated visual-review environment.
- A future V2 root must use `V2PortalBoundary` at its route boundary; applying it prematurely would style V1 descendants and is intentionally deferred.
- The component surface is intentionally foundational. FormField, Select/Combobox, DataTable/DataList, Drawer, and toast contracts remain future approved work, not placeholders.

**GATE 4A DESIGN SYSTEM FOUNDATION: PASS**
