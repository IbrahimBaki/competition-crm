# 11. Integrations

> **Backend availability — read before building.**
> Import preview and commit are **one endpoint**: `POST /import-runs` with
> `mode: "dry_run"` or `mode: "commit"` in the body. There are no
> `/import/preview` or `/import/commit` routes.

**Domains**: Integrations  
**Surface**: Staff CRM only  
**Permissions**: `integrations.*`

---

## API Tokens Manager

**Route**: `/admin/integrations/api-tokens`  
**Purpose**: Create and manage machine-to-machine API tokens  
**Permission**: `integrations.api_tokens.manage`  

**List of Tokens**:
| Name | Scope | Prefix | Expiry | Status | Actions |
|---|---|---|---|---|---|
| Mobile App | tickets.write, customers.read | [prefix]... | 2027-08-27 | Active ✓ | View / Revoke |
| Analytics Bot | reports.read | [prefix]... | 2026-09-15 | Expiring Soon ⚠️ | — |
| Legacy Sync | tickets.read, customers.read | [prefix]... | 2026-08-20 | Expired ❌ | Delete |

**Columns**:
- **Name**: descriptive label
- **Scope**: comma-separated list of assigned scopes (tickets.read/write, customers.read, knowledge.read, reports.read, webhooks.manage)
- **Prefix**: first 10 chars of token (full token never shown after creation)
- **Expiry**: expiration date, badge shows "Active", "Expiring Soon" (< 30 days), or "Expired"
- **Status**: active or expired
- **Actions**: View details, Revoke (delete), Regenerate (if expired)

**Actions** (per token):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| View | Modal showing token details (name, scope, created_at, expiry, last_used) | (no API call) | (same) | N/A |
| Revoke | Modal confirm + DELETE | `DELETE /integration/tokens/{token}` | (same) | Yes |

**Create Token Button**:

**Create Token Modal**:
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Name | string | Yes | Descriptive label (e.g., "Mobile App", "Webhook Bot") |
| Scopes | checkboxes | Yes | tickets.read, tickets.write, customers.read, knowledge.read, reports.read, webhooks.manage |
| Expiry | date picker | Yes | When token expires (max 2 years) |

**Scopes**:
- `tickets.read`: read-only access to tickets, messages, and related data
- `tickets.write`: can create/update tickets, post messages
- `customers.read`: read-only access to customer data
- `knowledge.read`: read-only access to published knowledge base
- `reports.read`: read-only access to reports
- `webhooks.manage`: create/manage webhook subscriptions

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create Token | POST token | `POST /integration/tokens` | (same) | No |

**Token Created Modal** (shown after POST success):
```
✓ Token Created Successfully

Name: Mobile App
Token: [full token displayed here — copy immediately]

⚠️ Copy this now — you won't see it again!

[Copy to Clipboard] [Done]
```

**Related endpoints**:
- `GET /integration/tokens` (list)
- `POST /integration/tokens` (create)
- `DELETE /integration/tokens/{token}` (revoke)

**Notes**:
- Token format: base64-encoded (32 chars, token prefix shown in list)
- Tokens are hashed in DB (never stored plaintext)
- Full token shown only once (on creation), after that only prefix visible
- Can be used for API authentication instead of user login (Bearer token auth)

---

## Webhooks Manager

**Route**: `/admin/integrations/webhooks`  
**Purpose**: Subscribe to CRM events and receive webhooks  
**Permission**: `integrations.webhooks.manage`  

**List of Subscriptions**:
| Event | Target URL | Status | Last Delivery | Failures | Actions |
|---|---|---|---|---|---|
| ticket.created | https://api.partner.com/webhooks | Active ✓ | 2 min ago | 0 | Edit / Disable / Delete |
| ticket.status_changed | https://erp.company.com/crm-sync | Active ✓ | 15 min ago | 2 | View Details |
| customer.merged | https://api.partner.com/webhooks | Disabled | — | — | Enable / Delete |

**Columns**:
- **Event**: webhook event type (ticket.created, ticket.updated, ticket.status_changed, ticket.message_posted, customer.*, sla.*, automation.rule_executed, etc.)
- **Target URL**: recipient endpoint
- **Status**: active, disabled, auto_disabled (too many failures)
- **Last Delivery**: timestamp + delivery state (sent, failed, retrying)
- **Failures**: count of consecutive failures
- **Actions**: view, edit, enable/disable, delete

**Actions** (per subscription):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| View Details | Modal showing subscription + recent deliveries | (no API call) | (same) | N/A |
| Edit | Modal to update target URL, event type, etc. | (PUT webhook) | (same) | N/A |
| Enable/Disable | Toggle active flag | (PATCH webhook) | (same) | Yes |
| Delivery Log | Navigate to detailed log (filtered by subscription) | `/admin/integrations/webhook-deliveries?filter[subscription_id]={id}` | (same) | N/A |
| Delete | DELETE | `DELETE /webhooks/subscriptions/{subscription}` | (same) | Yes |

**Create Subscription Button**:

**Create Webhook Modal**:
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Event Type | multi-select | Yes | ticket.created, ticket.updated, ticket.status_changed, ticket.message_posted, ticket.assigned, ticket.transferred, ticket.escalated, ticket.spam, customer.created, customer.updated, customer.merged, customer.blocked, sla.breached, automation.rule_executed, etc. |
| Target URL | URL | Yes | HTTPS endpoint (HTTP not allowed for security) |
| Secret (optional) | string | No | Shared secret for HMAC verification (X-Webhook-Signature header) |
| Active | checkbox | No | (default: checked) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create | POST webhook | `POST /webhooks/subscriptions` | (same) | No |

**Related endpoints**:
- `GET /webhooks/subscriptions` (list)
- `POST /webhooks/subscriptions` (create)
- `PATCH /webhooks/subscriptions/{subscription}` (enable/disable)
- `PUT /webhooks/subscriptions/{subscription}` (update)
- `DELETE /webhooks/subscriptions/{subscription}` (delete)

**Webhook Delivery Log**:
- URL: `/admin/integrations/webhook-deliveries`
- Table: timestamp, subscription, event, status (sent, failed, retrying), http_status, response, retries (e.g., "1/5")
- Filter: subscription, event type, status, date range
- Actions: [Retry Failed] button (per failed delivery)

**Retry Logic**:
- Up to 5 attempts over 24 hours (exponential backoff: 60s, 5m, 30m, 2h, 8h)
- Auto-disable subscription after 20 consecutive failures
- Manual retry available via [Retry] button

**Notes**:
- Webhook payload format: `{ event_type, data, timestamp, webhook_id }`
- Signature header: `X-Webhook-Signature: sha256={hmac_hex}` (computed with shared secret if provided)
- Timeout per delivery: 5 seconds
- Max payload size: 1 MB

---

## Bulk Import Wizard

**Route**: `/admin/integrations/import`  
**Purpose**: Import bulk data (customers, historical tickets)  
**Permission**: `integrations.import.manage`  

**Step 1: Choose Data Type**

**Radio buttons**:
- Customers (CSV)
- Historical Tickets (CSV)

**Step 2: Upload & Validate**

**File upload**:
- Drag-drop or click to select CSV file
- Max 50,000 rows per import
- Preview first 5 rows
- Start validation

**Validation Results**:
```
✓ File validated: 250 rows, 0 errors, 10 warnings

Warnings:
- Row 3: Email format unusual (flagged but will import)
- Row 15: Customer already exists (duplicate check)
```

**If errors**:
```
❌ Validation failed: 5 errors

Errors:
- Row 2: Missing required field "email"
- Row 8: Invalid phone format
- Row 12: Duplicate customer by email
```

**Action**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Continue (if warnings only) | Proceed to Step 3 | — | (same) | N/A |
| Fix & Re-upload (if errors) | Back to upload | — | (same) | N/A |

**Step 3: Preview & Choose Mode**

**Dry-Run Mode** (default):
```
Preview: Import would create 250 customers

[Sample Data Table]
Name        | Email              | Phone
Ahmed Ali   | ahmed@company.com  | +966501234567
...

[ Commit to DB ]  [ Dry-Run Again ]  [ Cancel ]
```

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Preview (Dry-Run) | Show what would be imported (no changes) | `POST /import-runs` (body `mode: "dry_run"`) | (same) | Yes |
| Commit | Actually import data | `POST /import-runs` (body `mode: "commit"`) | (same) | No (but idempotent by design via deduping) |

**Step 4: Progress & Results**

**Progress Bar**:
- Chunks processed: "250 / 500 rows (50%)"
- Estimated time remaining: "2 minutes"
- Live log: "Importing row 250...", "Row 300 created customer", etc.

**On Completion**:
```
✓ Import Complete

250 customers imported
0 failures
Duplicates merged: 5
Time elapsed: 4 minutes 32 seconds

[Import Another]  [View Imported]  [Done]
```

**Error Summary** (if any failures):
```
⚠️ 3 rows failed to import

Row 142: Customer email duplicate (skipped)
Row 203: Invalid phone format (skipped)
Row 315: Missing required field (skipped)

[Download Error Report (CSV)]
```

**Related endpoints**:
- `POST /import-runs` with `mode: "dry_run"` (validate + preview)
- `POST /import-runs` with `mode: "commit"` (actual import)
- (Response includes ImportRun object with `ImportRunRow` entries for each row + status)

**Notes**:
- CSV format: headers required (name, email, phone, etc.)
- Duplicate detection: by email or phone (configurable)
- Max chunk size: 500 rows per batch
- Retention: import runs + error reports stored for 90 days

---

## ERP Integration Context Panel (on Customer Profile)

**Route**: (Inline widget on `/crm/customers/{id}` detail, if ERP configured)  
**Purpose**: Show ERP-synced data about customer  
**Permission**: (implicit if ERP token configured)  

**Widget**:
```
🔗 ERP Sync
├─ Customer ID: ERP-12345
├─ Last Sync: 2 hours ago
├─ Status: ✓ Synced
└─ [View in ERP] [Sync Now] [Settings]
```

**Display** (if synced):
- ERP customer ID
- Last sync timestamp
- List of synced fields (company, billing address, payment terms, etc.)
- [Refresh] button to manually re-sync

**Related endpoints**:
- (ERP integration endpoint, likely `GET /customers/{id}/erp-context`)

**Notes**:
- Requires ERP config in admin settings (base URL, API key, auto-sync frequency)
- Webhook syncs customer updates from CRM → ERP in near-real-time
- Manual refresh available for testing

---

## Navigation Map

- **Sidebar: Admin > Integrations**
  - **API Tokens** → (list) → [Create] → **Create Token Modal**
  - **Webhooks** → (subscriptions list) → [Create] → **Create Webhook Modal**
    - (click subscription) → **Delivery Log** (filtered)
  - **Bulk Import** → **Import Wizard** (steps 1–4)
- **Customer Profile** → (ERP Context Panel widget, if enabled)

---

**Next**: [12-customer-portal.md](12-customer-portal.md)
