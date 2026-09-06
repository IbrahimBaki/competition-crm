# Gate 4H — Knowledge V2 report

## Inventory and boundaries

| Surface | Route | Data / behavior | Classification | Gate action |
| --- | --- | --- | --- | --- |
| Staff article ledger | `/knowledge` | Generated list/search hooks, server sort, page navigation | A | V2 ledger retained and completed |
| Staff article reading | `/knowledge/:articleId` | Article identity/body, preview, versions, state transitions | B | V2 reading frame; existing query/mutations retained |
| Staff create | `/knowledge/new` | Bilingual fields, categories, visibility, POST/invalidation | B | V2 route frame; legacy form controller retained |
| Staff edit | `/knowledge/:articleId/edit` | Existing record load, bilingual PATCH/invalidation | B | V2 route frame; legacy form controller retained |
| Category management | inline `/knowledge` | Existing hierarchy query and generic resource dialog | C | Kept isolated; no global restyle |
| Public help center | `/portal/help`, `/portal/help/:slug` | Public list/search/read/feedback | C | Not touched (Portal V2 out of scope) |

The staff router guards remain unchanged: view, create, update, publish, archive, version restore, and category-management permissions are supplied by the existing router and action controls. The article edit link now also observes the existing update permission.

## Actual product behavior discovered

- Staff API: categories, list, search, detail, create, update, state transition, version list/restore, and rendered-reply preview.
- State behavior is draft → in review → published and archive; no delete endpoint exists. Attachments, article relations, article-level category labels, and author editing are not present in the supplied resource contract.
- The editor is a route-modal, bilingual textarea form; there is no rich-text editor, autosave, attachment uploader, or separate history editor to migrate.
- Ticket Workbench has no discovered staff Knowledge UI integration. Portal uses separate public routes and remains visually and behaviorally isolated.

## Implementation

Modified:

- `src/features/knowledge/KnowledgeListV2.tsx`
- `src/features/knowledge/KnowledgeV2.module.css`
- `src/pages/operations/CreationPages.tsx`
- `src/i18n/en.json`
- `src/i18n/ar.json`

Created: no new source file. No files were deleted.

The list is an open, ruled Signal Ledger table with server-backed search, server-backed sorting when not searching, pagination, translated labels, semantic status text, and a mobile ledger reflow. The reading route gives the actual article body the primary plane, with contextual state, visibility, real author and timestamp metadata, followed by the retained preview and lifecycle/history controls. Create and edit now have route-local V2 boundaries without changing their data controller, API calls, validation, serialization, or invalidation.

## Evidence

- Responsive CSS covers the 1024 rail pressure point and reflows the list at `<=768px`; controls retain 40px minimum height and mobile pagination remains reachable.
- RTL is passed to each staff Knowledge V2 boundary. Arabic body content gets an intrinsic RTL reading direction, while dates retain the existing `ds-bidi-value` isolation.
- Accessibility additions: a table caption, labelled search/sort fields, labelled pagination navigation, visible focus styling, semantic article/main landmarks, and permission-gated edit action. Existing AsyncBoundary preserves loading/error/empty behavior.
- `npm run typecheck`: pass.
- `npm run lint`: pass.
- `npm run build`: pass (existing bundle-size advisory only).
- Focused i18n guard: pass.
- `npx playwright test e2e/core-workflows.spec.ts --grep "bilingual knowledge"`: pass; it creates and updates a test-owned bilingual article through the real backend.
- Full `npm test`: 161 passed, 3 failed. The two accepted `src/features/admin/api/__tests__/wire.test.ts` failures remain; the additional Knowledge hard-coded-string failure found during this gate was fixed and the targeted guard now passes.
- Impeccable detector: no findings on changed Knowledge files. The UI UX Pro Max review was applied for labelled controls, keyboard/focus access, responsive ledger reflow, touch sizing, and native RTL ordering.

## Remaining limitations / disposition

- No dedicated Knowledge unit test was added; the real backend E2E is the focused coverage.
- No standalone axe LTR/RTL browser run was completed in this pass.
- The shared category dialog, lifecycle panel, preview panel, and textarea form retain their legacy presentation internally; only the Knowledge-route framing and list/detail hierarchy are V2. Restyling those shared pieces safely needs a dedicated component migration, not a global visual override.
- A full rendered 1440/1024/768/375 and Arabic browser critique/audit was not completed. Therefore the strict Gate 4H completion bar is not met.

## Scores

| Measure | Score |
| --- | ---: |
| Signal Ledger fidelity | 8.6 |
| Knowledge findability | 8.8 |
| List scan efficiency | 8.8 |
| Article readability | 8.5 |
| Content/action hierarchy | 8.4 |
| Editing usability in scope | 7.4 |
| Responsive / RTL / accessibility evidence | 7.8 |
| Logic and permission preservation | 9.1 |
| Portal isolation | 9.5 |
| Avoids generic CMS/SaaS | 8.7 |
| Overall Knowledge V2 confidence | 8.3 / 10 |

GATE 4H KNOWLEDGE V2: FAIL
