# Support CRM v1 API Collection

Runnable REST collection that exercises the primary API flows end-to-end.

## Prerequisites

### Database Setup
```bash
# Initialize database with seeded demo data
php artisan migrate:fresh --seed
```

This command:
- Resets all tables
- Runs all migrations
- Seeds with demo data including staff user, departments, branches, etc.

### Seeded Credentials

The `DatabaseSeeder` chain creates the following test accounts:

| Account | Email | Password | Role |
|---------|-------|----------|------|
| Admin (Staff) | `admin@support.local` | `password` | Super Admin |
| Portal Customer | `contact@acme.local` | `acme-portal-pass` | Customer |

**Important**: These credentials are hardcoded only for testing. They're automatically created by seeders and cleaned on `migrate:fresh`.

### Environment

- **Base URL**: `http://localhost:8000/api/v1`
- **Language**: English (`en`) — use `ar` for Arabic in `Accept-Language` header
- **Idempotency**: All write operations carry `Idempotency-Key` headers (UUID format)

## Running the Collection

### VS Code REST Client

1. Install [REST Client](https://marketplace.visualstudio.com/items?itemName=humao.rest-client) extension
2. Open `support-crm-v1.http` in VS Code
3. Click "Send Request" above each request block

### IntelliJ HTTP Client

1. Open `support-crm-v1.http` in IntelliJ/WebStorm
2. Click the ▶️ icon next to each request
3. Results appear in the HTTP client panel

### Command Line (httpyac)

```bash
npm install -g httpyac
httpyac docs/api/collections/support-crm-v1.http
```

Or use the built-in HTTP runner in your IDE.

## Collection Flow

The file is organized as a sequence of 17 requests:

1. **Staff Login** → Get bearer token
2. **Create Customer** → Store customer ID
3. **Create Ticket** → Store ticket ID
4. **Post Public Message** → Customer-visible reply
5. **Post Internal Note** → Staff-only note
6. **Assign Ticket** → Assign to agent
7. **Transfer Department** → Move between departments
8. **Get Ticket Detail** → Fetch ticket (with SLA info)
9. **Change Status** → Mark as "In Progress"
10. **Portal: Customer Login** → Get portal token
11. **Portal: Get My Tickets** → List customer's tickets
12. **Portal: Get Ticket Detail** → Verify internal notes NOT visible
13. **Portal: Get Messages** → Verify only public messages appear
14. **Public: Submit Web Form** → Create ticket from public form
15. **Get Dashboard Report** → Fetch management dashboard
16. **Issue Integration Token** → Create API token
17. **Call with API Token** → Use machine-to-machine token

## Expected Results

All requests should return:
- **2xx status** for successful operations (200, 201, 204)
- **Documented response envelope** matching `ErrorResponse` or data schema
- **Internal notes** NEVER visible to portal users
- **Portal attachments** access guarded to ticket owner
- **API tokens** only usable with granted scopes

## Troubleshooting

### "email already exists" on step 2
Run `php artisan migrate:fresh --seed` to reset and reseed the database.

### Bearer token invalid (401)
- Ensure step 1 (Staff Login) succeeded and returned a token
- Check that the token is being inserted into the `Authorization: Bearer` header automatically

### Portal login fails (403)
- Portal customer must be created via the staff API first (step 2 creates the customer)
- Ensure the customer contact's email matches the portal login email
- Portal auth requires `Accept-Language` header

### "Ticket not found" (404)
- Ensure step 3 (Create Ticket) ran and stored the ticket ID
- If using in isolation, manually update `@ticketId` at the top

## Integration Notes

- **Idempotency**: Duplicate requests with the same `Idempotency-Key` return the same 2xx response
- **Pagination**: List endpoints support `?page=1&per_page=10&sort=-created_at`
- **Filtering**: Use `?filter[status]=open` syntax for nested object filters
- **Language**: `Accept-Language: ar` returns Arabic UI strings; `en` returns English

## Testing in CI

This collection is paired with `tests/Feature/Api/PrimaryFlowSmokeTest.php`, which runs the same flows in PHPUnit to verify API contract stability.
