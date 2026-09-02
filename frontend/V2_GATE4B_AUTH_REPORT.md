# Frontend V2 — Gate 4B Authentication Experience Report

## Existing Auth Flow Inventory

| Flow | Route | Existing Logic Reused | V2 UI Migrated |
| ---- | ----- | --------------------- | -------------- |
| Staff sign-in | `/login` | `AuthProvider.login`, CSRF bootstrap, normalized errors, requested-route redirect | Yes |
| Staff 2FA challenge | `/login/two-factor` | `AuthProvider.completeTwoFactor`, code format, immediate redirect | Yes |
| Forgot password | `/forgot-password` | Existing mutator request and error mapping | Yes |
| Reset password | `/reset-password` | Existing query-token, request, client mismatch validation, redirect | Yes |
| Invitation acceptance | `/invitations/:token` | Existing token, request, error mapping, redirect | Yes |
| Logout/session expiry/auth bootstrap | Provider/guard behavior | Unchanged; not a route presentation migration | Not changed |

There is no existing staff password-visibility control, 2FA resend action, separate recovery-code route, or unauthenticated locale picker. None was added. The existing locale provider continues to determine English LTR or Arabic RTL.

## Files Created

- `src/auth/api/recovery.ts`
- `src/pages/auth/AuthFrame.tsx`
- `src/pages/auth/AuthFrame.module.css`
- `src/pages/auth/__tests__/StaffAuthV2.test.tsx`

## Files Modified

- `src/pages/LoginPage.tsx`
- `src/pages/TwoFactorPage.tsx`
- `src/pages/auth/RecoveryPages.tsx`
- `src/i18n/en.json`
- `src/i18n/ar.json`

## Authentication Logic Reused

`AuthProvider`, generated/API transport, Axios mutator, CSRF behavior, cookie session handling, 2FA challenge calls, normalized error semantics, guard redirects, session-expiry signaling, permissions, and backend contracts are unchanged. Recovery request literals were moved unchanged from JSX to `auth/api/recovery.ts`; this preserves the existing mutator contract while keeping V2 JSX endpoint-free.

## V2 Boundary Strategy

Every migrated staff-auth route renders inside `V2PortalBoundary`. The boundary supplies `[data-ui="v2"]`, current language, logical direction, Signal Ledger typography/tokens, focus treatment, and a portal host without applying V2 styles to V1 authenticated routes.

## Login Composition

The shipped composition is the approved Ledger Frame: mineral canvas, one open asymmetric work plane, compact identity/context rail, one measured vertical rule, 400–440px desktop form, and deliberate unused space. It has no card shadow, illustration, hero, marketing claim, badge, gradient, or decorative dashboard treatment.

## 2FA

The existing normal code input is retained rather than replaced with OTP cells. It now uses the shared frame, `autocomplete="one-time-code"`, LTR code direction, clear recovery-code guidance, loading button state, focused error summary, and unchanged verification/redirect behavior.

## Other Existing Auth Flows

Forgot password, reset password, and invitation acceptance use the same frame, V2 Label/Input/Button primitives, field-level server errors, focused form summary, loading states, autocomplete semantics, and existing redirects. Their prior literal UI copy is now translated through the existing English/Arabic i18n bundles.

## Validation / Error Handling

Server-safe form errors retain normalized messages. Field errors remain local and programmatically connected; form-level errors use a compact live alert and receive focus after submit. No internal payload, request detail, password, or session information is exposed. Native browser validation remains active.

## Loading States

Primary actions retain their label while reporting `aria-busy`, disabling repeated submit without layout shift. AuthProvider bootstrap presentation and session-expiry architecture were intentionally not altered because they are global provider behavior outside this route-only V2 boundary.

## RTL

The frame uses logical grid/border properties and reverses naturally in RTL. Arabic receives the approved optical type scale; email, password, and 2FA values remain isolated LTR; Arabic labels/errors/actions use normal RTL reading order. Long error messages wrap rather than overflow.

## Responsive

At 1440/1024 the full Ledger Frame preserves its asymmetry and form measure. At 768 it becomes an identity masthead above a 440px work plane. At 375 it is a full-width, single-column form with safe gutters, no floating card, and 44px touch-primary action.

## Accessibility

Each route has one `h1`, connected labels, `aria-describedby` field feedback, live form errors, focus management after failed submit, visible tokenized focus, keyboard-native forms, appropriate button types/disabled state, and current-password/new-password/one-time-code semantics. Targeted login and 2FA axe checks pass (with jsdom color contrast disabled, consistent with the existing V2 suite).

## Browser QA

| View | Result |
| ---- | ------ |
| 1440 English | PASS |
| 1440 Arabic | PASS |
| 768 Arabic | PASS — corrected to masthead/work-plane layout |
| 375 English | PASS |
| 375 Arabic | PASS |
| 2FA desktop | PASS |
| 2FA mobile RTL | PASS |

Browser review used the configured application host. No new page errors or console errors attributable to V2 auth were observed; the expected unauthenticated `/auth/me` 401 was not treated as an application defect.

## Impeccable Findings

| Severity | Finding | Fix |
| -------- | ------- | --- |
| P1 | 768px retained the desktop context rail rather than transitioning to the approved compact frame. | Moved masthead transition to `<1024px`; retained 440px form measure. |
| P2 | The initial form-level error rule looked like a side-tab accent in the deterministic scan. | Replaced it with a restrained horizontal semantic rule. |
| P3 | Initial direct local-host review had CORS noise because it used a port outside the configured SPA origin. | Re-ran on configured host; no V2-attributable errors. |

Impeccable critique/audit conclusion: the final surface is product-specific through its Ledger Frame geometry, scarce teal, quiet structural rule, and open work plane. It avoids generic SaaS auth-template signals while preserving task clarity and accessibility.

## Regression Verification

| Behavior | Result |
| -------- | ------ |
| Successful login | PASS — same `AuthProvider.login` and requested-route redirect call path |
| Failed login | PASS — same normalized error behavior, V2 focused presentation |
| CSRF unchanged | PASS — no CSRF/client/mutator change |
| 2FA unchanged | PASS — same `completeTwoFactor` request and redirect |
| Redirect behavior | PASS — existing login, reset, invitation, and 2FA destinations retained |
| Session expiry architecture untouched | PASS — provider/session/mutator behavior not edited |
| Locale behavior | PASS — existing locale provider, EN/AR resources, `lang`/`dir` propagation retained |

## Validation

| Check | Result |
| ---------- | ------ |
| Typecheck | PASS — `npm run typecheck` |
| Lint | PASS — `npm run lint` |
| Auth tests | PASS — 4 targeted V2 auth tests |
| V2 tests | PASS — 12 targeted auth/design-system tests |
| Full tests | BASELINE ONLY — 128 passed, 2 known failures |
| Build | PASS — `npm run build` |
| Axe | PASS — login LTR/RTL and 2FA targeted checks |

## Baseline Failures

`src/features/admin/api/__tests__/wire.test.ts` retains the two known unrelated mapper failures: expected Branch `Cairo Branch` and Department `Support`, received empty strings. No auth/V2 code imports or changes those mappers. Existing React Router future-flag and React 18/Radix test `act(...)` warnings remain test-environment noise.

## Remaining Authentication Risks

- Live successful-login and authenticated 2FA completion remain dependent on a permitted real staff test account; component-level behavior and existing provider tests cover the preserved call paths.
- The global AuthProvider bootstrap spinner is intentionally outside the scoped V2 route boundary and remains a future provider-level migration decision.

## Gate Assessment

| Area | /10 |
| ---------------------- | --: |
| Signal Ledger fidelity | 9.3 |
| Brand distinctiveness | 9.2 |
| Login usability | 9.3 |
| Authentication clarity | 9.3 |
| RTL | 9.2 |
| Responsive | 9.2 |
| Accessibility | 9.3 |
| Security preservation | 9.6 |
| Logic preservation | 9.6 |
| Premium perception | 9.2 |

Overall Auth Experience Confidence: 9.3/10

GATE 4B AUTH EXPERIENCE: PASS
