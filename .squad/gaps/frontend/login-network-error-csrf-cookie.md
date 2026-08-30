# Gap: Frontend login fails — missing .env + cross-origin session cookie

## Found
1. `frontend/.env` does not exist (only `.env.example`), so `VITE_API_BASE_URL`
   / `VITE_API_PREFIX` are undefined at runtime, producing requests to the
   literal path `/undefinedundefined/sanctum/csrf-cookie` (404) from
   `http://localhost:5174/login`. Mechanical setup gap.
2. Even with `.env` populated, login will still silently fail: the frontend
   is 100% Sanctum cookie-session auth (confirmed — grepped `src/` for
   `Authorization`/`Bearer`/`meta.token`: never read or set anywhere).
   `localhost:5174` and the real backend host (`competition-crm.azmsquad.localhost`,
   per `.env`'s `APP_URL` and the live `competition-crm.conf` Apache vhost) are
   different sites, and Sanctum's `EnsureFrontendRequestsAreStateful` hardcodes
   the session cookie to `SameSite=Lax`, which browsers never send cross-site.
3. A prior story (`.squad/plans/frontend/31-story-478.md`, "FE-01") already
   designed a domain-split fix for exactly this (`docker/env-additions.txt`,
   `docker/vhost-competition-crm.conf`), but hardcoded the wrong hostname
   (`support-crm.localhost` / `app.support-crm.localhost`), which collides
   with a *different*, unrelated Laravel project already deployed on this
   machine under that name (`/var/www/html/support-crm`, repo
   `IbrahimBaki/Support-crm`). That config was never applied to this
   project's actual `.env`, and the implementation report's own "Verify
   Local Dev" step was never completed.

## Asked
Presented 3 options for how to resolve the cross-origin cookie problem:
  A. Access the frontend via the backend's own hostname on a different port
     (`http://competition-crm.azmsquad.localhost:5174/login`) — same-site to
     the browser since only the port differs. Needs: `frontend/.env`, a Vite
     `server.allowedHosts` tweak, and `SANCTUM_STATEFUL_DOMAINS` /
     `CORS_ALLOWED_ORIGINS` updated in the backend `.env`. No new vhost.
  B. Reverse-proxy the Vite dev server behind a new subdomain vhost inside
     `azm-php82` (e.g. `app.competition-crm.azmsquad.localhost` →
     `localhost:5174`), mirroring the original FE-01 design but with the
     correct hostname. Needs Apache `mod_proxy`/`mod_proxy_wstunnel` for
     Vite's HMR websocket to survive the proxy.
  C. Build the frontend (`npm run build`) and serve `frontend/dist` as
     static files from the existing `competition-crm.conf` vhost (or the
     already-drafted `docker/vhost-competition-crm.conf` SPA vhost) — true
     single-origin, no CORS/SameSite question at all, but loses Vite HMR
     during active frontend development.

## Decided
User later said "proceed and fix issues" without picking A/B/C explicitly.
Went with **Option A** (same hostname, different port) — smallest change,
no new vhost/proxy, keeps Vite HMR.

## Done
- Created `frontend/.env` with `VITE_API_BASE_URL=http://competition-crm.azmsquad.localhost`.
- Backend `.env`: added `SANCTUM_STATEFUL_DOMAINS` (includes
  `competition-crm.azmsquad.localhost:5174`) and
  `CORS_ALLOWED_ORIGINS=http://competition-crm.azmsquad.localhost:5174`.
  Documented both (with the *why*, generically, no hardcoded hostname) in
  `.env.example` since `.env` itself is a guarded/no-edit path.
- `frontend/vite.config.ts` **and** `frontend/vite.config.js` (Vite prefers
  the compiled `.js` over `.ts` when both exist — had to patch both):
  added `server.allowedHosts: ['competition-crm.azmsquad.localhost']`.
- Ran `php artisan config:clear`, killed and restarted the Vite dev server
  so both env files took effect.

**Found and fixed a second, deeper bug while verifying** (would have kept
login broken even with the above): `AuthController::login()` never called
`Auth::login($user)` — it only minted a Sanctum bearer token the frontend
never reads (frontend is 100% cookie-session, confirmed earlier). So even
with CORS/cookies/CSRF all correct, `/auth/me` right after login returned
401 forever. Fixed by adding `Auth::login($user)` (+ `session()->regenerate()`
guarded by `$request->hasSession()`, since this same endpoint must also stay
crash-free for non-stateful/bearer-only callers per the earlier
`Session store not set on request` fix in this same file). Also fixed
`logout()`: with a session-authenticated request, `currentAccessToken()`
returns Sanctum's `TransientToken`, which has no `delete()` — was a
guaranteed 500 on every real-browser logout. Guarded with an
`instanceof PersonalAccessToken` check before deleting.

**Verified end-to-end via curl** (real cookie jar, `Origin`/`Referer` set to
`http://competition-crm.azmsquad.localhost:5174` to match what a browser at
that origin sends): `/sanctum/csrf-cookie` → 204 with `Set-Cookie` echoing
the SPA origin in `Access-Control-Allow-Origin` → `POST /auth/login` → 200
→ `GET /auth/me` → 200 (previously 401) → `POST /auth/logout` → 204
(previously 500) → `GET /auth/me` → 401 (session correctly torn down).

Ran `vendor/bin/pest --filter="LoginTest|AuditTrailAuthTest|TwoFactorPolicyTest"`
before and after (via `git stash`): identical 5 pre-existing failures both
times (unrelated FK/status-code issues already known from other gap docs)
— no regression. `test_logout_invalidates_session` (previously the same
500-crash class of bug) now passes.

**Not done / still open:**
- `/etc/hosts` — not needed on this machine (`.localhost` TLD resolves via
  systemd-resolved without an entry), but flagging in case another
  developer's machine needs one added manually for
  `competition-crm.azmsquad.localhost`.
- Options B and C were not implemented; if a proper subdomain split is
  wanted later (e.g. for prod-like local parity), this decision can be
  revisited.
