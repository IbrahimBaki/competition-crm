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
Not yet — user asked to log this as a gap instead of picking now.

## Done
Nothing implemented. `frontend/.env` still does not exist; no backend
`.env`, Apache vhost, or Vite config changes made. Awaiting a decision on
A/B/C above before any fix is implemented.
