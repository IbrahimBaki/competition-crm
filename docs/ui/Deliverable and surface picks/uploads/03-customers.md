# 03. Customers

**Domains**: Customers  
**Surface**: Staff CRM only  
**Permissions**: `customers.*`

---

## Customer List Screen

**Route**: `/crm/customers`  
**Purpose**: Browse, search, and filter all customers  
**Permission**: `customers.view`  

**Fields** (table columns):
| Field | Type | Sortable | Filterable | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes (text search) | Clickable to detail |
| Email (Primary) | string | No | Yes | First email contact |
| Phone (Primary) | string | No | No | First phone contact, masked |
| Company | string | No | Yes | Company name (if B2B linked) |
| Status | enum | Yes | Yes | active, blocked, anonymised |
| Tickets (Count) | number | Yes | No | Number of tickets |
| Last Contact | datetime | Yes | No | Most recent ticket/message |

**List Controls**:
- **Pagination**: page, per_page (default 25)
- **Sort**: by name, status, last_contact, ticket_count
- **Filter**: status, company, date range (created/last_contact)
- **Search**: global text search across name, email, phone

**Actions** (per row):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| View | Navigate to customer detail | `/crm/customers/{id}` | (same) | N/A |

**Bulk Actions**:
- [ ] Checkbox select multiple
- **Block Selected** (if permission `customers.block`)
- **Export** (CSV of selected)

**Related endpoints**:
- `GET /customers` (list with pagination/filter/sort)

**Notes**:
- Anonymised customers: show as "Anonymised User" (no email/phone exposed)
- Blocked customers: show with red badge, cannot create new tickets

---

## Customer Profile Screen

**Route**: `/crm/customers/{id}`  
**Purpose**: Full 360° view of customer + manage details, contacts, notes, attachments  
**Permission**: `customers.view`  

**Header Section**:
- Name, email (primary), phone, company
- Status badge (active/blocked/anonymised)
- Service tier (Standard/Priority/VIP — used by SLA)
- [Block Customer] [Merge] [Export] action buttons (permission-gated)

**Tabs**:

### Tab 1: Details
**Read-only fields**:
- Name, preferred locale, created_at, last_contact_at
- Company account (linked, if B2B)

**Editable fields** (if `customers.update`):
- Name
- Preferred Locale (en/ar)
- Service Tier (Standard/Priority/VIP)

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Show form (inline edit or modal) | `PATCH /customers/{customer}` | `customers.update` | No |
| Block | Modal confirm + POST block | `POST /customers/{customer}/block` with reason | `customers.block` | Yes |
| Unblock | POST unblock | `POST /customers/{customer}/unblock` | `customers.block` | Yes |

**Related endpoints**:
- `GET /customers/{id}` (detail)
- `PATCH /customers/{customer}` (update)
- `POST /customers/{customer}/block` (with `blocked_reason` field)
- `POST /customers/{customer}/unblock`

---

### Tab 2: Contacts
**Purpose**: Manage customer's contact channels (email, phone, WhatsApp, SMS, etc.)  

**Table**:
| Type | Value | Label | Primary | Verified | Actions |
|---|---|---|---|---|---|
| Email | ahmed@example.com | Work | ☑ | ✓ | Edit / Delete |
| Phone | +966501234567 | Mobile | ○ | — | — |
| WhatsApp | +966501234567 | — | ○ | — | — |
| SMS | +966501234567 | — | ○ | — | — |

**Actions** (per row, if `customers.contact.manage`):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal to edit contact details | `PUT /customers/{customer}/contacts/{contact}` | (same) | No |
| Set Primary | Make this the primary contact | (implicit in edit) | (same) | N/A |
| Delete | DELETE | `DELETE /customers/{customer}/contacts/{contact}` | (same) | Yes |

**Add Contact Modal**:
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Type | enum | Yes | email, phone, whatsapp, sms, portal_login, web_form, chat |
| Value | string | Yes | Email/phone number validated per type |
| Label | string | No | e.g., "Work", "Mobile", "Personal" |
| Is Primary | checkbox | No | Only one can be primary |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Add | POST contact | `POST /customers/{customer}/contacts` | (same) | No |

**Related endpoints**:
- `GET /customers/{customer}/contacts` (list)
- `POST /customers/{customer}/contacts` (create)
- `PUT /customers/{customer}/contacts/{contact}` (update)
- `DELETE /customers/{customer}/contacts/{contact}` (delete)

---

### Tab 3: Notes
**Purpose**: Internal notes about customer (not visible to customer)  

**Timeline**:
- Each note shows: author name, timestamp, content, [Delete] button
- Newest first

**Add Note Form**:
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Note | textarea | Yes | Plain text or markdown |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Add Note | POST note | `POST /customers/{customer}/notes` | `customers.note.create` | No |
| Delete | DELETE | `DELETE /customers/{customer}/notes/{note}` | `customers.note.delete` | Yes |

**Related endpoints**:
- `GET /customers/{customer}/notes` (list)
- `POST /customers/{customer}/notes` (create)
- `DELETE /customers/{customer}/notes/{note}` (delete)

---

### Tab 4: Attachments
**Purpose**: Manage customer profile attachments (documents, files)  

**Grid**:
- Thumbnails of files (with type icons: PDF, image, etc.)
- [+ Upload] button
- [Delete] action on each

**File Upload** (if `customers.attachment.manage`):
- Drag-and-drop or click to upload
- Max 10 MB per file
- Shows scanning state after upload
- On success, file appears in grid

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Upload | POST attachment | `POST /attachments` (generic) + link to customer | (same) | No |
| Delete | DELETE | `DELETE /customers/{customer}/attachments/{attachment}` | (same) | Yes |

**Related endpoints**:
- `GET /customers/{customer}/attachments` (list)
- `POST /attachments` (generic upload)
- `DELETE /customers/{customer}/attachments/{attachment}` (delete)

---

### Tab 5: Timeline (360° Activity)
**Purpose**: Chronological feed of all customer interactions  

**Timeline entries** (newest first):
- Ticket created, message posted, contact added, note added, status changed, merged, etc.
- Each entry: timestamp, icon (ticket, message, contact, note), description, [details] expand

**Filterable by**:
- Event type (ticket, message, contact, note, etc.)
- Date range

**Related endpoints**:
- `GET /customers/{customer}/timeline` (list activity)

---

### Tab 6: Tickets
**Purpose**: List of all tickets for this customer  

**Quick table** (or link to filtered ticket list):
- Reference, subject, status, priority, created_at, last_message_at
- Clickable to ticket detail

**Related endpoints**:
- `GET /tickets?filter[customer_id]={customer_id}` (list tickets for customer)

---

## Customer Create Screen

**Route**: `/crm/customers/new`  
**Purpose**: Create new customer record  
**Permission**: `customers.create`  

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | No | Full name |
| Preferred Locale | enum | Yes | No | en, ar (default: org default) |
| Company | select | No | No | Optional B2B link |
| Email (Primary) | string | Yes | No | Validated |
| Phone (Primary) | string | No | No | Optional |
| Service Tier | enum | No | No | Standard (default), Priority, VIP |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create | POST customer | `POST /customers` | (same) | No |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /customers`

**Notes**:
- On create, primary email/phone are added as contacts automatically
- Customer auto-created when first ticket/web form submission arrives; this screen is for manual creation

---

## Customer Duplicates Screen

**Route**: `/crm/customers/duplicates`  
**Purpose**: Review and merge duplicate customer records  
**Permission**: `customers.duplicate.review`  

**List of Duplicate Candidates**:

| Candidate 1 | vs | Candidate 2 | Status | Actions |
|---|---|---|---|---|
| Ahmed Ali (ahmed@mail.com) | | Ahmed Ali (ahmed.ali@mail.com) | pending | Merge / Dismiss |
| Sarah Smith (sarah@co.com) | | Sara Smith (sara@co.com) | pending | — |

**Actions** (per duplicate pair):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| View Side-by-Side | Link to comparison page | `/crm/customers/duplicates/{candidatePair}/compare` | (same) | N/A |
| Merge | POST merge | `POST /customers/{primary}/merge` with `merge_into_customer_id` | (same) | Yes |
| Dismiss | POST dismiss | `POST /customers/duplicates/{candidate}/dismiss` | (same) | Yes |

**Related endpoints**:
- `GET /customers/duplicates` (list candidates)
- `POST /customers/{primary}/merge` (merge two customers)
- `POST /customers/duplicates/{candidate}/dismiss` (mark as not a duplicate)

---

## Customer Merge Comparison Screen

**Route**: `/crm/customers/duplicates/{candidate}/compare`  
**Purpose**: Side-by-side comparison before merging  

**Side-by-side layout**:

```
Field           | Customer A        | Customer B
Name            | Ahmed Ali         | Ahmed Ali ✓ (radio)
Email           | ahmed@mail.com    | ahmed.ali@mail.com ✓
Phone           | (none)            | +966501234567 ✓
Tickets         | 5                 | 3 (both will transfer to A)
```

**Merge Behavior**:
- User selects which value to keep for each field (radio buttons)
- All tickets from Customer B reassign to Customer A
- Customer B is marked as merged (deactivated, but record preserved)
- Audit log records the merge

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Confirm Merge | POST merge | `POST /customers/{A}/merge` with `{merge_into_customer_id: B, field_selections: {...}}` | (same) | Yes |
| Cancel | Go back | — | N/A | N/A |

---

## Navigation Map

- **Sidebar: Customers** → **Customer List** (search/filter)
  - (click customer) → **Customer Profile** (tabs: Details, Contacts, Notes, Attachments, Timeline, Tickets)
    - (edit) → form (inline or modal)
    - (block) → modal confirm
    - (merge) → **Duplicate Review** or direct to comparison
- **Sidebar: Customers > Duplicates** → **Duplicates Screen**
  - (click pair) → **Merge Comparison** → confirm → back to duplicates list
- **Create Customer**: via **Customer Create Screen** or auto-created from ticket/web form

---

**Next**: [04-tickets.md](04-tickets.md)
