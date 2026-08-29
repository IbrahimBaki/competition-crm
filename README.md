# Support CRM

A Laravel-based customer support management system with a React SPA frontend, supporting multilingual (AR/EN) content and role-based access control.

## Architecture

- **Backend**: Laravel 12 API (`/api/v1/...`) served at `competition-crm.azmsquad.localhost`
- **Frontend**: React 18 SPA served at `app.competition-crm.azmsquad.localhost`
- **Authentication**: Sanctum cookie-based (stateful)
- **Database**: MySQL in Docker
- **API Client**: Generated from OpenAPI spec with orval

## Prerequisites

- Docker + Docker Compose
- Node.js 18+ (for frontend development)
- Make (for convenience commands)

## Local Development Setup

### 1. Backend Setup

```bash
# Install dependencies
make install
# or: docker exec -w /var/www/html/competition-crm azm-php82 composer install

# Generate app key (if needed)
make key-generate

# Setup database
make migrate
make seed

# Run tests
make test
make lint
```

### 2. Frontend Setup

```bash
cd frontend

# Install dependencies
npm ci

# Generate API client from OpenAPI spec
npm run api:generate

# Start dev server (port 5174)
npm run dev

# Run tests, typecheck, and lint
npm run test && npm run typecheck && npm run lint
```

### 3. Local Hostnames

Add to `/etc/hosts`:
```
127.0.0.1  competition-crm.azmsquad.localhost app.competition-crm.azmsquad.localhost
```

Then open:
- **Frontend (SPA)**: http://app.competition-crm.azmsquad.localhost
- **Vite development**: http://app.competition-crm.azmsquad.localhost:5174
- **API**: http://competition-crm.azmsquad.localhost/api/v1/
- **Mailpit (email preview)**: http://localhost:8025

## API Contract

### Success Response
```json
{
  "data": { /* resource or array of resources */ },
  "meta": {
    "request_id": "uuid",
    "page": 1,
    "per_page": 25,
    "total": 150,
    "total_pages": 6
  },
  "links": {
    "self": "...",
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  }
}
```

### Error Response
```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The given data was invalid.",
    "request_id": "uuid",
    "field_errors": {
      "email": ["Email is required", "Email must be unique"]
    }
  }
}
```

### List Endpoints
- `?page=N` - page number (default 1)
- `?per_page=M` - items per page (default 25, max 100)
- `?sort=field1,-field2` - sorting (comma-separated, `-` for descending)
- `?filter[status]=open&filter[q]=search` - filtering
- `?include=related_entity` - eager-load relationships

## Domain Split Configuration

For cookie-based authentication across the domain split:

- `SESSION_DOMAIN`: `.competition-crm.azmsquad.localhost` (shared parent domain with leading dot)
- `SANCTUM_STATEFUL_DOMAINS`: `app.competition-crm.azmsquad.localhost,app.competition-crm.azmsquad.localhost:5174`
- `CORS_ALLOWED_ORIGINS`: `http://app.competition-crm.azmsquad.localhost,http://app.competition-crm.azmsquad.localhost:5174`

**Important**: Deploy backend config **before** publishing SPA vhost, or users will see login loop.

## Frontend API Client Generation

The frontend uses **orval** to generate a TypeScript client from `docs/api/openapi.yaml`:

```bash
cd frontend
npm run api:generate
```

**Critical**: `frontend/src/api/generated/` is produced by orval and committed to Git. Never edit by hand. Re-run after any contract change and commit the diff.

## Conventions

See `.claude/skills/` for detailed guidelines:
- `permission-scope`: Permissions are keys like `tickets.view.department`, never role names
- `api-contract`: Unified envelope and error shape across all endpoints
- `bilingual-i18n`: Admin-created fields are `{ar, en}` objects, never hardcoded text
- `audit-trail`: Sensitive changes logged to audit table
- `upload-security`: Allowlist + virus scan + storage outside web root
- `working-time-sla`: Use `WorkingTimeService`, never `now()->diffInMinutes()`

## Make Targets

### Backend
```bash
make install         # composer install
make key-generate    # php artisan key:generate
make migrate         # php artisan migrate
make seed            # php artisan db:seed
make test            # vendor/bin/pest
make lint            # vendor/bin/phpstan analyse
```

### Frontend
```bash
make fe-install      # cd frontend && npm ci
make fe-generate     # cd frontend && npm run api:generate
make fe-test         # cd frontend && npm run test
make fe-build        # cd frontend && npm run build
make fe-dev          # cd frontend && npm run dev
```

## Troubleshooting

### Login immediately bounces to login page
- Check `SESSION_DOMAIN` matches `.competition-crm.azmsquad.localhost`
- Check `SANCTUM_STATEFUL_DOMAINS` includes the active SPA origin
- Check CORS `exposed_headers` includes `X-Request-Id`
- Check vhost serves from `frontend/dist` for SPA (not `public/`)

### Generated API client is missing types
- Run `npm run api:generate` from `frontend/` directory
- Check `docs/api/openapi.yaml` is valid (no OpenAPI 3.1-only constructs)
- Check `orval.config.ts` path aliases match

### Environment variables not loading
- For Docker: add to `docker/env-additions.txt` and rebuild containers
- For local: copy `.env.example` to `.env` and edit
- For vhost/CORS config: restart Apache after env changes

## Deployment

### Frontend Build
```bash
cd frontend
npm run build
# Output: frontend/dist/
```

Deploy `frontend/dist/` to the SPA vhost at `app.<subdomain>`.

### Backend
```bash
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
```

Ensure:
- `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`, `CORS_ALLOWED_ORIGINS` are set for production
- Session cookie is `secure: true` in production
- Frontend origin is whitelisted in CORS

## Testing

### Backend
```bash
make test           # Run test suite
make lint           # Run static analysis
```

### Frontend
```bash
cd frontend
npm run test        # Unit + integration tests (vitest)
npm run typecheck   # TypeScript strict mode
npm run lint        # ESLint + no-role-names rule
npm run build       # Verify build succeeds
```

## Resources

- Backend: `docs/contracts/conventions-digest.md`
- API: `docs/api/openapi.yaml` + `docs/api/openapi.v1.frozen.yaml`
- UI: `docs/ui/00-overview.md` and module-specific files
- Permissions: `app/Domains/Security/Permissions/PermissionKey.php`
- Example requests: `docs/api/collections/support-crm-v1.http`
