# 04. Tickets (Core Workbench)

> **Backend availability — read before building.**
> **Saved views have no API.** A `ticket_saved_views` table and a
> `TicketSavedView` model exist, but no routes are registered, so the Saved
> Views screens cannot be built yet.
> Queues expose only `GET /tickets/queues/mine` and
> `GET /tickets/queues/department/{department}` — there is **no team queue**.
> Ticket statuses cannot be deleted (categories can).

**Domains**: Ticketing  
**Surface**: Staff CRM only  
**Permissions**: `tickets.*`, `ticket.*`

---

## Ticket Queues Screen

**Route**: `/crm/tickets/queues` or `/crm/tickets`  
**Purpose**: Agent/manager workbench; choose which queue to view  
**Permission**: `tickets.view.{own|team|department|any}` (determines visible queues)  

**Queue Selector** (left sidebar or top nav dropdown):
```
My Tickets        [shows if: tickets.view.own]
├─ Open (5)
├─ Pending (2)
└─ Resolved (0)

Team Tickets      [shows if: tickets.view.team]
└─ Open (12)

Department        [shows if: tickets.view.department]
├─ Support (8)
├─ Billing (3)
└─ Technical (1)

All Tickets       [shows if: tickets.view.any]
├─ Open (45)
├─ Pending (10)
└─ Resolved (0)
```

**Selection**:
- Click a queue to load its ticket list
- Counts update in real-time (optional: via WebSocket or polling every 30s)

**Related endpoints**:
- `GET /tickets/queues/mine` (my tickets)
- `GET /tickets/queues/team/{team}` ⚠️ **NOT IMPLEMENTED** (team tickets, if applicable)
- `GET /tickets/queues/department/{department}` (department tickets)
- (implicit queue for all tickets via `GET /tickets?filter[status]=open` etc.)

---

## Ticket List Screen (per Queue)

**Route**: `/crm/tickets?filter[status]=open&filter[queue]=department` (etc.)  
**Purpose**: Paginated, sortable, filterable list of tickets  
**Permission**: Based on queue scope (`tickets.view.{own|team|department|any}`)  

**Columns** (customizable via preferences):
| Column | Type | Sortable | Filterable | Notes |
|---|---|---|---|---|
| Reference | string | Yes | No | Ticket number (TKT-001), clickable to detail |
| Subject | string | Yes | Yes (search) | Truncated; click to detail |
| Customer | string | No | Yes (search/select) | Name + email |
| Status | enum | Yes | Yes | active badge color |
| Priority | enum | Yes | Yes | icon + color |
| Category | enum | Yes | Yes | bilingual name |
| Assigned To | string | No | Yes (user select) | Agent avatar + name, or "Unassigned" |
| SLA Status | enum | Yes | No | Badge: Green/Yellow/Red (running/warning/breached) + time |
| Created | datetime | Yes | Yes (date range) | Relative time (e.g., "2d ago") |
| Last Message | datetime | Yes | No | Relative time |
| Tags | string | No | Yes (tag search) | Comma-separated, clickable |

**List Controls**:
- **Pagination**: page, per_page (default 25, max 100)
- **Sort**: click column header, visual indicator (↑↓), multi-column sort (optional: Shift+click)
- **Filter panel** (collapsible):
  - Status: checkboxes (new, open, pending, resolved, closed, spam)
  - Priority: checkboxes (low, normal, high, urgent)
  - Category: multi-select or checkboxes (bilingual names)
  - Assigned To: user select (including "Unassigned")
  - SLA Status: checkboxes (running, warning, breached, met)
  - Date Created: date range picker
  - Tags: tag autocomplete (multi-select)
  - Free-text search: searches subject + body + customer name

**Saved Views** (if `tickets.view.own` at minimum):
- [My Open Tickets] (default saved view)
- [High Priority] (quick filter)
- [SLA Warning] (SLA status = warning)
- [+ Save Current View] button to create custom saved view

**Bulk Actions** (if checkboxes selected):
- [ ] Checkbox per row
- **Bulk Assign** (if `tickets.assign`): modal to select agent
- **Bulk Status Change** (if `tickets.status.change`): dropdown to select new status
- **Bulk Priority** (if `tickets.update`): dropdown
- **Bulk Add Tag** (if `tickets.tag`): tag input
- **Bulk Export** (CSV)

**Related endpoints**:
- `GET /tickets` (list with pagination/filter/sort)
- `POST /tickets/{ticket}/assign` (bulk assign, loop)
- `POST /tickets/{ticket}/status` (bulk status, loop)
- `GET /ticket-saved-views` ⚠️ **NOT IMPLEMENTED** (list saved views)

**Notes**:
- Empty state: "No tickets match your filters" + clear-all-filters button
- Loading: skeleton table (10 rows) + spinner, expected ~100ms (from perf budget)
- Dashboard link: can add widget "SLA Warnings" that links to pre-filtered queue

---

## Ticket Detail Screen

**Route**: `/crm/tickets/{id}` or `/crm/tickets/TKT-{reference}`  
**Purpose**: Full ticket view with conversation, SLA info, actions  
**Permission**: Based on ticket scope (`tickets.view.{own|team|department|any}`)  

**Header** (sticky):
- **Reference** (TKT-001), clickable back breadcrumb
- **Subject** (editable if `tickets.update`)
- **Status badge** (clickable for status change if `tickets.status.change`)
- **Priority badge** (clickable for priority change if `tickets.update`)
- **SLA Countdowns** (two badges: first_response, resolution — SLACountdownBadge component)
  - Running (green), Warning (yellow ≥80%), Breached (red)
  - Remaining time, click for details (policy name, target, elapsed, etc.)

**Quick Action Bar**:
- [Reply] button (main action, focus)
- [Assign To] dropdown (if `tickets.assign`, shows agent list or [Claim] if unassigned)
- [Transfer to Agent] dropdown (if `tickets.transfer.agent`)
- [Transfer to Department] dropdown (if `tickets.transfer.department`)
- [Status] dropdown (if `tickets.status.change`)
- More menu: [Reopen], [Spam], [Link], [Merge], [Split], [Watchers], [Escalate]

**Main Content Area** (left ⅔):

### Section 1: Ticket Details (Collapsible)
- **Created**: by whom, when
- **Category** (editable dropdown if `tickets.reclassify`)
- **Tags** (editable, multi-select if `tickets.tag`)
- **Custom Fields** (if category has any — e.g., Department, Product, Version)
- **Channels**: how ticket was created (email, web form, portal, chat, etc.)

### Section 2: Conversation Thread
**Messages** (threaded, newest at bottom):
- Each message shows: avatar, author name, timestamp, body, [delete] (if own message or admin)
- **Internal Notes** (staff-only): visually distinct (e.g., yellow background, icon indicating internal)
- **Attachments**: thumbnails inline (with status badge: pending/clean/infected)
- **Message Status Badges**: queued, sent, delivered, read, failed (with retry button if failed)

**Message Compose Area**:
- **Rich text editor** (bold/italic/lists, paste images)
- **Attachments**: click to upload (or drag-drop)
- **Visibility toggle**: "Public" vs "Internal Note" (only appears for agents)
- [Send] button (disabled if no content)
- Character counter (optional)

**Compose Actions** (if permission allows):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Send Reply | POST message with `visibility: public` | `POST /tickets/{ticket}/messages` | `ticket.message.send` | No (needs Idempotency-Key) |
| Send Internal Note | POST message with `visibility: internal` | `POST /tickets/{ticket}/messages` | `ticket.message.internal_write` | No |
| Insert Quick Reply | Click quick-reply chip | (autocomplete quick replies) | `workspace.quick_replies.view` | N/A |

**Related endpoints**:
- `GET /tickets/{ticket}` (detail + SLA)
- `GET /tickets/{ticket}/messages` (conversation thread)
- `POST /tickets/{ticket}/messages` (post message/note)
- `GET /tickets/{ticket}/history` (activity log)

---

**Sidebar** (right ⅓):

### Ticket Info Panel
| Field | Type | Editable | Notes |
|---|---|---|---|
| Customer | string | No | Name + avatar, link to customer profile |
| Email | string | No | Primary contact |
| Phone | string | No | Primary contact |
| Created | datetime | No | Absolute + relative |
| Assigned To | string (agent avatar) | Yes (if `tickets.assign`) | Dropdown to change agent |
| SLA Policy | string | No | Policy name + link to policy detail |
| Linked Tickets | count | No | Number of linked tickets, click to expand |
| Watchers | count | No | Agents watching this ticket |

### Activity Timeline (Collapsible)
- Chronological list of all ticket events: created, assigned, status_changed, message_posted, sla_breached, escalated, etc.
- Click event to see before/after state (for status/priority changes)
- Scrollable, newest at bottom

**Related endpoints**:
- `GET /tickets/{ticket}/history` (event timeline)

---

## Ticket Create Screen

**Route**: `/crm/tickets/new` or embedded in customer profile  
**Purpose**: Create manual ticket (not from channel)  
**Permission**: `tickets.create`  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Customer | select (autocomplete) | Yes | Existing customer or create inline |
| Subject | string | Yes | Ticket subject line |
| Body | textarea | Yes | Ticket body/description |
| Category | select | Yes | Hierarchical (from TicketCategory) |
| Priority | enum | No | low, normal, high, urgent (default: normal) |
| Assigned To | select | No | Agent (if not set, unassigned) |
| Tags | multi-select | No | Quick categorization |
| Attachments | file upload | No | Drag-drop area |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create Ticket | POST ticket | `POST /tickets` | (same) | No (needs Idempotency-Key) |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /tickets`

---

## Ticket Status Change Screen

**Route**: Modal or inline dropdown on ticket detail  
**Purpose**: Change ticket status with validation  
**Permission**: `tickets.status.change`  

**Workflow**:
1. Click Status badge on ticket detail
2. Modal shows allowed next statuses (based on current status + ticket state)
3. Select new status
4. Optional: add message to ticket when status changes ("Moving to Resolved")
5. Submit

**State Machine** (enforced server-side, UI shows only allowed transitions):
```
new ──→ open ──→ pending ──→ resolved ──→ closed
 └──────────┘      ↑
                   └─ open (reopen, if within reopen_window_days)

Also: any status → spam (mark) and spam → previous_status (restore)
```

**Related endpoints**:
- `POST /tickets/{ticket}/status` (change status)
- `POST /tickets/{ticket}/reopen` (reopen from closed/resolved, within 14 days)
- `POST /tickets/{ticket}/spam` (mark as spam)
- `DELETE /tickets/{ticket}/spam` (restore from spam)

---

## Ticket Categories Screen (Admin)

**Route**: `/admin/tickets/categories`  
**Purpose**: Manage ticket categories + custom fields  
**Permission**: `tickets.categories.manage`  

**Category Tree** (hierarchical, drag-to-reorder):
```
Support
├─ Technical
├─ Billing
└─ General
Sales
├─ Inquiry
└─ Demo
[+ Add Category]
```

**Actions** (per category):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal/navigate | `/admin/tickets/categories/{id}` | (same) | N/A |
| Delete | DELETE (if no tickets use it) | `DELETE /ticket-categories/{category}` | (same) | Yes |
| Add Sub-Category | Link | — | (same) | N/A |

**Category Edit Modal**:
**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Category label (ar/en) |
| Code | string | Yes | No | Unique identifier |
| Parent Category | select | No | No | For hierarchical nesting |

**Custom Fields** (per category):
- Table of fields: name, type (text/number/date/select/boolean), required, validation
- [+ Add Field] to add more
- Each field edit: modal for detailed config (options for select type, etc.)

---

## Ticket Statuses Screen (Admin)

**Route**: `/admin/tickets/statuses`  
**Purpose**: Configure ticket status catalogue  
**Permission**: `tickets.statuses.manage`  

**Status List** (ordered):
| Name | Lifecycle Type | Is Default | Is System | Actions |
|---|---|---|---|---|
| New | new | ✓ | ✓ | View |
| Open | open | — | ✓ | Edit / Delete (can't delete system) |
| Pending | pending | — | ✓ | — |
| Resolved | resolved | — | ✓ | — |
| Closed | closed | — | ✓ | — |
| Spam | spam | — | ✓ | — |
| [+ Add Custom Status] | — | — | — | — |

**Actions** (per status):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal to edit bilingual name, lifecycle type | `PATCH /ticket-statuses/{status}` | (same) | No |
| Delete | (only custom statuses) | `DELETE /ticket-statuses/{status}` ⚠️ **NOT IMPLEMENTED** | (same) | Yes |

**Status Edit Modal**:
**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Status label (ar/en) |
| Lifecycle Type | enum | Yes | No | new, open, pending, resolved, closed, spam (determines workflow) |
| Is Default | checkbox | No | No | Auto-assign to new tickets if checked |
| Position | number | No | No | Sort order in UI |

**Related endpoints**:
- `GET /ticket-statuses` (list)
- `POST /ticket-statuses` (create)
- `PATCH /ticket-statuses/{status}` (update)
- `DELETE /ticket-statuses/{status}` ⚠️ **NOT IMPLEMENTED** (delete)

---

## Ticket Saved Views Screen

**Route**: `/crm/tickets/saved-views`  
**Purpose**: Manage custom ticket filters  
**Permission**: `tickets.view.*` (at least one scope)  

**List of Saved Views**:
| Name | Filters | Owner | Actions |
|---|---|---|---|
| My Open Tickets | status=open, assigned=me | System | Default (can't delete) |
| High Priority | priority=high | Me | Edit / Delete |
| SLA Warning | sla_status=warning | Me | — |

**Actions** (per view):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Load View | Navigate to ticket list with filters applied | (navigation) | (same) | N/A |
| Edit | Modal to update filters | `PATCH /ticket-saved-views/{view}` ⚠️ **NOT IMPLEMENTED** | (same) | No |
| Delete | DELETE | `DELETE /ticket-saved-views/{view}` ⚠️ **NOT IMPLEMENTED** | (same) | Yes |

**Create/Edit View Modal**:
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Name | string | Yes | View label |
| Filters | form | Yes | Status, Priority, Category, etc. (same as ticket list filters) |

---

## Ticket Feedback Screen (from Portal)

**Route**: `/crm/tickets/{id}/feedback`  
**Purpose**: View customer satisfaction feedback on resolved tickets  
**Permission**: `tickets.feedback.view`  

**Read-only display**:
- Star rating (1–5)
- Comment (if provided)
- Submission timestamp

**Related endpoints**:
- `GET /tickets/{ticket}` (includes feedback if posted)

---

## Navigation Map

- **Sidebar: Tickets** → **Queues** (My/Team/Department/All)
  - (select queue) → **Ticket List** (paginated, filtered)
    - (click ticket) → **Ticket Detail** (conversation, SLA, actions)
      - (reply) → **Compose Area** (send message/note)
      - (status) → **Status Change Modal**
      - (assign) → **Assign Modal**
      - (escalate) → **Escalation Modal** (see 05-sla-automation.md)
      - (link/merge/split) → **Linking/Merge/Split Modals**
- **+ Create Ticket**: **Ticket Create Screen** (manual ticket entry)
- **Sidebar: Admin > Tickets** → statuses/categories screens
- **Saved Views**: accessible from queue selector or ticket list toolbar

---

**Next**: [05-sla-automation.md](05-sla-automation.md)
