# V2 Phase 0 — BLOCKER-001 Report

## Root Cause

The mismatch was confirmed. `src/api/http/mutator.ts` emitted `window.dispatchEvent(new Event('session:expired'))` after failed non-portal 401 recovery, while `src/auth/AuthProvider.tsx` subscribed only to the separate in-memory `on('session:expired', ...)` registry from `src/auth/session.ts`. No bridge existed.

Normal staff 401 handling already attempted `recoverSession()`. The mutator deduplicated concurrent callers with module-level `recoveryInFlight`; `session.ts` deduplicated the CSRF plus `/auth/me` recovery itself. Recovery success retried the original request. Recovery failure sent the unconsumed browser event, leaving a possible stale authenticated provider/query state. The focus/visibility `/auth/me` revalidation also bypassed the mutator and swallowed genuine 401s.

## Files Changed

| File | Change |
|---|---|
| `src/auth/session.ts` | Added canonical `expireSession()`: an in-memory signal emitted once per authenticated session. A new authenticated session resets the latch; the generic emitter is internal. |
| `src/api/http/mutator.ts` | Replaced browser-event dispatch with `expireSession()` after failed eligible staff 401 recovery. |
| `src/auth/AuthProvider.tsx` | Sends only normalized unauthenticated focus/visibility revalidation failures through `expireSession()`. Existing provider cleanup/redirect remains owner-controlled. |
| `src/auth/__tests__/session.test.ts` | Added signal idempotency and listener-unsubscribe coverage. |
| `src/api/http/__tests__/mutator.test.ts` | Added staff 401, successful recovery, concurrent 401, 419 retry, and portal-isolation coverage. |
| `src/auth/__tests__/AuthProvider.session-expiry.test.tsx` | Added provider cleanup, focus revalidation, and unmount subscription cleanup coverage. |

## Final Session Expiry Flow

```text
staff API request
    ↓
401 (eligible non-portal endpoint)
    ↓
deduplicated recovery attempt
    ├── succeeds → retry original request; no expiry signal
    └── fails
          ↓
     expireSession() once per authenticated session
          ↓
     AuthProvider in-memory subscription
          ├── clears user and session state
          ├── clears React Query cache
          ├── marks status unauthenticated
          └── redirects to /login?reason=session_expired
```

Focus/visibility revalidation retains its 30-second throttle and direct `/auth/me` request. It now sends only a normalized `unauthenticated` result into the same signal; validation, 403, 404, server, network, and successful 419 recovery remain non-expiry cases.

## Why This Implementation Was Chosen

The existing typed in-memory session registry is now the single authority because it is what `AuthProvider` already consumes. The import is acyclic: `mutator → auth/session → api/http/{client,csrf,envelope}`; `session.ts` never imports the mutator. This removes the unconsumed browser channel rather than maintaining two unrelated mechanisms.

The per-session latch resides in `session.ts`, so normal request recovery and focus revalidation cannot produce duplicate provider reactions. `AuthProvider` remains solely responsible for clearing user/query state and redirecting. Concurrent callers can reach `expireSession()`, but only the first emits, yielding one provider cache clear and redirect.

## Portal Isolation

Portal behavior was not changed. `/portal/*` receives a bearer token only on portal requests and remains excluded from staff recovery/expiry. The new regression test confirms a portal 401 neither calls staff recovery nor emits staff expiry. Portal storage, routes, and backend contracts were untouched.

## Tests Added / Updated

| Test file | Coverage |
|---|---|
| `src/auth/__tests__/session.test.ts` | One emission per session, reset after a new authenticated session, unsubscribe behavior. |
| `src/api/http/__tests__/mutator.test.ts` | Unrecoverable staff 401; successful recovery; deduplicated concurrent recovery/one final signal; successful 419 retry; portal isolation and bearer attachment. |
| `src/auth/__tests__/AuthProvider.session-expiry.test.tsx` | Signal clears authenticated state and Query cache; focus 401 uses same flow; unmount removes listener. |

## Validation Results

| Command | Result | Notes |
|---|---|---|
| Focused Vitest suite | PASS | 10 tests passed. |
| `npm test` | FAIL — pre-existing unrelated failure | 116/118 tests passed. Two failures in `src/features/admin/api/__tests__/wire.test.ts` expect branch/department names but receive empty strings. This change does not modify or import that mapper. Existing React `act` and Router future-flag warnings were also emitted. |
| `npm run typecheck` | PASS | No TypeScript errors. |
| `npm run lint` | PASS | No lint warnings/errors. |
| `npm run build` | PASS | `tsc -b && vite build` completed. |

## Remaining Risks

The retained redirect uses `window.location.href` to preserve current public-route behavior. Browser E2E validation against a deliberately expiring real backend session is still useful, but there is no remaining source-level staff-session signalling mismatch.

V2 BLOCKER-001 STATUS: RESOLVED
