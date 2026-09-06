# Frontend V2 — Gate 4G Customer Report

## Discovered Route and Data Inventory

| Area | Existing behavior retained | V2 scope |
| --- | --- | --- |
| `/customers` | URL-backed search, status filter, server sort, pagination | Route-local V2 list ledger |
| `/customers/:customerId` | customer query/adapter, contacts, notes, attachments, ERP, duplicates, timeline, block/unblock | Route-local V2 record composition |
| `/customers/new` | Permission-gated V1 `RouteFormModal` | Intentionally unchanged |
| Ticket Workbench context | Existing customer link/context | No Workbench redesign; canonical navigation remains compatible |

The customer list API returns raw list rows only. It does not expose primary contact, company account, service tier, or related tickets; no customer-health metrics or related-ticket ledger was fabricated.

## Presentation Classification

- A — customer-route presentation: `CustomersPage`, `CustomerDetailPage`, list/search/table presentation were migrated locally.
- B — customer presentation with retained business logic: contacts, notes, attachments, duplicates, ERP, timeline, and block/unblock now use the Customer-local ruled Signal Ledger treatment while preserving their existing generated queries, mutations, guards, and errors.
- C — shared presentation: `/customers/new` remains its deliberately unchanged V1 `RouteFormModal`. Shared confirmation and upload logic gained optional Customer-local presentation hooks only; no shared/global V1 restyle was made.

## Files Created

- `src/pages/CustomersPageV2.module.css`
- `src/pages/CustomerDetailPageV2.module.css`
- `src/features/customers/list/CustomerListTable.module.css`
- `src/features/customers/list/CustomerSearchBar.module.css`
- `src/features/customers/detail/CustomerRecordV2.module.css`
- `e2e/customers-v2.spec.ts`

## Files Modified

- `src/pages/CustomersPage.tsx`
- `src/pages/CustomerDetailPage.tsx`
- `src/features/customers/list/CustomerListTable.tsx`
- `src/features/customers/list/CustomerSearchBar.tsx`
- `src/features/customers/detail/CustomerHeader.tsx`
- `src/features/customers/detail/CustomerIdentityPanel.tsx`
- `src/features/customers/detail/CustomerNotesPanel.tsx`
- `src/features/customers/detail/CustomerAttachmentsPanel.tsx`
- `src/features/customers/detail/CustomerTimeline.tsx`
- `src/features/customers/detail/ErpContextPanel.tsx`
- `src/features/customers/detail/CustomerDangerActions.tsx`
- `src/features/customers/detail/CustomerStatusBadge.tsx`
- `src/features/customers/duplicates/DuplicateCandidatesPanel.tsx`
- `src/shared/attachments/AttachmentUploader.tsx`
- `src/shared/confirm/ConfirmActionDialog.tsx`
- `src/i18n/en.json`
- `src/i18n/ar.json`

## Files Deleted

None.

## Data / Logic Reused

Generated customer clients, `toCustomerDetail`/`toCustomerListRow`, `useCustomerListQuery`, URL state, query keys, customer mutations, permission guards, CSRF/session behavior, route contracts, localization, and existing attachment/merge/block semantics are unchanged.

## V2 Boundary Strategy

`/customers` and `/customers/:customerId` each use route-local `V2PortalBoundary`. The authenticated shell remains V2, while unrelated siblings and `/customers/new` remain outside the Customer route boundary.

## Information Architecture

The list is a flat operational ledger: name, actual customer status, and created date with server-supported sorting. The detail surface uses a primary record flow for identity, notes, attachments, and duplicate review; ERP context and timeline remain a subordinate inspector. This intentionally avoids invented related tickets, account metrics, or customer-health widgets.

## Create / Edit Scope

`/customers/new` remains the existing V1 RouteFormModal because it is shared route-form behavior outside the safe route-local Customer presentation migration. No edit route exists in the discovered router.

## Arabic Rendered Evidence / RTL and Bidi Review

Authenticated browser evidence used the accessible real record **Ada Lovelace** at canonical route `/customers/8d6883fd-501c-4482-af7a-ab5b0ad45c0e`. Arabic list and populated detail passed at 1440, 768, and 375. The detail visibly contained the identity, active status, email (`probe-fix-verify@example.com`), identity/contact content, notes, attachments, duplicates, ERP context, and activity history within `dir="rtl"`; no capture was loading, forbidden, error, not-found, or an empty route shell.

The 1024 English check passed at the existing 248px Staff Rail pressure point without any Staff Shell change. All inspected 1440/1024/768/375 list/detail documents had no horizontal overflow. Arabic uses logical layout, LTR bidi isolation for email and dates/times, `overflow-wrap` for long record values, and localized custom attachment selection. The available real record did not expose a long Arabic name, attachment filename, URL, or ERP identifier, so those unavailable payload cases are recorded as truthful evidence limits rather than fabricated test data.

## Accessibility and Responsive Design

The desktop list is semantic table markup with sortable headers and `aria-sort`; at `<=768` it becomes labelled ledger rows with a block label/value separation. Controls have visible labels and keyboard operation. The detail has one top-level application main landmark (the nested page `main` was removed), labelled file inputs, semantic headings, named actions, visible focus inherited from V2, and route-local RTL direction.

Injected `axe-core` coverage on populated V2 regions passed with zero violations in English/LTR and Arabic/RTL for both list and detail. The audit specifically resolved the former duplicate/nested main landmark and unlabeled upload-control violations. Colour-contrast remains disabled in injected axe runs because this test environment does not load the production web fonts consistently; semantic token pairs and browser rendering were manually reviewed.

## Validation

| Check | Result |
| --- | --- |
| Typecheck | PASS — final revalidation |
| Lint | PASS — final revalidation |
| Focused customer tests | PASS — 30 tests / 7 files |
| Build | PASS — production Vite build |
| Axe LTR / RTL | PASS — populated list + detail, zero violations in both locales |
| Full suite | PASS with accepted baseline only — 162 passed / 2 failed across 39 files |
| Customer E2E / browser QA | PASS — 5/5; authenticated list → real canonical detail plus 1440/1024/768/375 no-overflow checks |
| Arabic browser evidence | PASS — populated list + canonical detail at 1440/768/375 |
| Impeccable rendered review | PASS — independent dual-agent rendered critique/audit completed; remediation re-reviewed live |
| UI UX Pro Max final analytical check | PASS — final hierarchy, responsive, RTL, a11y and cognitive-load review completed |

## Accepted Baseline Failures

The only accepted full-suite failures remain `src/features/admin/api/__tests__/wire.test.ts`: Branch name expected `"Cairo Branch"`, receives `""`; Department name expected `"Support"`, receives `""`.

## Rendered Impeccable Critique and Remediation

Method: dual-agent (Assessment A: `/root/impeccable_design_review` · Assessment B: `/root/impeccable_audit`). Assessment A performed the unanchored real-data design review; Assessment B independently ran the detector and live browser evidence before remediation. The detector initially returned no customer source findings. Its final supplementary scan reports four false-positive `gray-on-color` warnings against compatibility selectors for inherited shared-upload/dialog Tailwind class names; they do not identify rendered grey-on-colour text and axe/browser review found no corresponding issue.

| Severity | Rendered finding | Resolution |
| --- | --- | --- |
| P1 | Detail visually embedded V1 cards, default controls and saturated action bars across contacts, notes, attachments, duplicates, ERP, timeline and block/unblock. | Resolved through Customer-local `CustomerRecordV2` ruled record treatment; no business behavior or global shared treatment was changed. |
| P2 | Mobile ledger labels visually ran into values. | Resolved: labels now take their own logical block before the value. |
| P2 | Arabic attachment picker exposed browser-default English text. | Resolved: a local labelled trigger uses Arabic/English translations while preserving the native input and upload flow. |
| P2 | Detail contained an extra main landmark and a label-less fallback file input. | Resolved; populated axe is clean in LTR and RTL. |

Final rendered critique: the list reads as a compact Signal Ledger rather than generic cards; search/filter/sort/pagination and row navigation remain clear. The record has identity first, notes and attachments in primary operational flow, duplicates after current work, and ERP/history as subordinate inspector context. Contacts, notes, attachments, duplicates, ERP, timeline, block/unblock, confirmation, and upload affordances now share the same record grammar; no unresolved visible V1 Customer presentation remains. No P0/P1 or material P2 remains.

## UI UX Pro Max Final Review

The final review confirms clear customer identity/status at the record start, one deliberately quiet destructive action, operationally scannable ruled groups, native mobile ordering, readable Arabic hierarchy, isolated LTR values, and 44px-touch-primary actions. Notes and attachments are appropriately prominent relative to available data; ERP and history remain secondary. The design stays consistent with Signal Ledger and the Tickets/Workbench operational density without copying unsupported related-ticket, account, tier, or health data.

## Truthful Limitations

- Customer list/detail APIs do not provide related tickets, list-row primary contact, company account/service tier, or customer-health metrics. None was fabricated.
- The accessible real record had a Latin name and no attachment/URL/ERP identifier, so those specific long mixed-content payloads could not be visually proven from real data.
- `/customers/new` intentionally remains V1 outside this route-local migration. This is not embedded in either migrated Customer route.

## Gate Assessment

| Area | /10 |
| --- | --: |
| Signal Ledger fidelity | 9.2 |
| Information hierarchy | 9.1 |
| Customer identity clarity | 9.2 |
| Related-work usefulness (relative to available data) | 9.0 |
| Action hierarchy | 9.0 |
| Responsive behavior | 9.3 |
| Mobile usability | 9.1 |
| RTL | 9.1 |
| Accessibility | 9.2 |
| Permission correctness | 9.5 |
| Logic preservation | 9.5 |
| Avoids generic CRM UI | 9.2 |
| Premium perception | 9.1 |

Overall Customer V2 Confidence: 9.2/10

GATE 4G CUSTOMER V2: PASS
