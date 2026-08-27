# 06. Channels

**Domains**: Channels (Email, WebForm, Messaging, Chat sub-domains)  
**Surface**: Staff CRM only  
**Permissions**: `channels.*`

---

## Web Form Builder Screen

**Route**: `/admin/channels/web-forms`  
**Purpose**: Create/manage public intake forms  
**Permission**: `channels.web_form.manage`  

**List of Forms**:
| Name | Department | Status | Fields | Created | Actions |
|---|---|---|---|---|---|
| Contact Us (تواصل معنا) | Support | Active | 5 | 2026-08-01 | Edit / Publish / Delete |
| Billing Issue | Billing | Draft | 3 | 2026-08-15 | Edit / Publish / Delete |

**Actions** (per form):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to form editor | `/admin/channels/web-forms/{id}` | (same) | N/A |
| Publish | Toggle active/draft | `PATCH /channels/web-forms/{form}` (set status) | (same) | No |
| Delete | DELETE | `DELETE /channels/web-forms/{form}` | (same) | Yes |
| View Submissions | Navigate to submissions log | `/admin/channels/web-forms/{id}/submissions` | (same) | N/A |

**Related endpoints**:
- `GET /channels/web-forms` (list)

---

## Web Form Edit Screen

**Route**: `/admin/channels/web-forms/new` or `/admin/channels/web-forms/{id}`  
**Purpose**: Create/edit public intake form  
**Permission**: `channels.web_form.manage`  

**Section 1: Basic Info**

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Form title (ar/en) |
| Description | textarea | No | Yes | Intro text shown above form |
| Department | select | Yes | No | Route submissions to this department |
| Default Category | select | Yes | No | Auto-assign to this ticket category |
| Default Priority | enum | Yes | No | low, normal, high, urgent |
| Status | enum | No | No | Draft (hidden) or Published (public) |

---

**Section 2: Fields Builder** (Visual, drag-to-reorder)

```
[+ Add Field]

Field 1:  [Label: Name (ar/en)]  [Type: text]  [Required: ✓]  [Delete] [↑↓]
Field 2:  [Label: Email]  [Type: email]  [Required: ✓]  [Delete] [↑↓]
Field 3:  [Label: Subject]  [Type: text]  [Required: ✓]  [Delete] [↑↓]
Field 4:  [Label: Message]  [Type: textarea]  [Required: ✓]  [Delete] [↑↓]
Field 5:  [Label: Category]  [Type: select]  [Required: ✓]  [Delete] [↑↓]
           [Options: Technical, Billing, General]
Field 6:  [Label: Priority]  [Type: select]  [Required: —]  [Delete] [↑↓]
           [Options: Low, Normal, High, Urgent]
```

**Field Types**:
- text, textarea, email, phone, number, date, select, multiselect, boolean (checkbox)

**Field Editor Modal** (click field to edit):
**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Label | string | Yes | Yes | Field label (ar/en) |
| Type | enum | Yes | No | text, textarea, email, phone, number, date, select, multiselect, boolean |
| Required | checkbox | No | No | Mark as mandatory |
| Validation | string | No | No | Regex pattern or preset (email, phone, etc.) |
| Options | string (comma-separated) | If type=select/multiselect | Possibly bilingual | List of choices |
| Default Value | string | No | No | Pre-fill value |
| Placeholder | string | No | Yes | Hint text (ar/en) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save Form | PATCH/POST form | `POST /channels/web-forms` or `PATCH /channels/web-forms/{form}` | (same) | No |
| Preview | Show live form preview (modal) | (no API call) | (same) | N/A |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /channels/web-forms`, `PATCH /channels/web-forms/{form}`, `GET /channels/web-forms/{id}`

---

## Web Form Submissions Log

**Route**: `/admin/channels/web-forms/{form-id}/submissions`  
**Purpose**: View submissions received from public form  
**Permission**: `channels.web_form.view`  

**Table** (or card grid):
| Submitted | Name | Email | Subject | Ticket Created | Status |
|---|---|---|---|---|---|
| 2026-08-27 | Ahmed | ahmed@mail.com | Technical Issue | TKT-042 | ✓ Created |
| 2026-08-26 | Sarah | sarah@co.com | Billing Query | Pending | ⏳ Duplicate |

**Related endpoints**:
- `GET /channels/web-forms/{form}/submissions` (list)

---

## Email Inbound Log

**Route**: `/admin/channels/email/inbound`  
**Purpose**: View, inspect, and replay inbound emails  
**Permission**: `channels.email.replay.list`  

**Table**:
| From | Subject | Received | Classification | Ticket | Status | Actions |
|---|---|---|---|---|---|---|
| ahmed@acme.com | RE: Support Request | 2026-08-27 10:30 | reply | TKT-038 | ✓ | Inspect / Replay |
| noreply@system.com | Delivery Failed | 2026-08-27 10:00 | auto_reply | — | ⚠️ | Inspect |

**Filters**:
- Classification (reply, new, auto_reply, bounce, loop)
- Status (received, processed, suppressed, failed)
- Date range
- Sender search

**Actions** (per email):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Inspect | Modal showing raw email (headers, body, parsed fields) | (no API call, data in list) | (same) | N/A |
| Replay | Re-process email (create new ticket or message) | `POST /channels/email/inbound/{record}/replay` | `channels.email.replay.action` | Yes |

**Related endpoints**:
- `GET /channels/email/inbound` (list)
- `POST /channels/email/inbound/{record}/replay` (replay email)

---

## Message Templates Manager (WhatsApp/SMS)

**Route**: `/admin/channels/messaging/templates`  
**Purpose**: Create/manage outbound message templates  
**Permission**: `channels.messaging.templates.manage`  

**List of Templates**:
| Name | Type | Language | Status | Created | Actions |
|---|---|---|---|---|---|
| Ticket Confirmed | WhatsApp | en | Active | 2026-08-01 | Edit / Delete |
| Ticket Confirmed | WhatsApp | ar | Active | 2026-08-01 | — |
| SLA Warning | SMS | en | Active | 2026-08-15 | Edit / Delete |

**Actions** (per template):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to template editor | `/admin/channels/messaging/templates/{id}` | (same) | N/A |
| Delete | DELETE | `DELETE /provider-message-templates/{template}` | (same) | Yes |

**Related endpoints**:
- `GET /messaging/templates` (list)

---

## Message Template Edit Screen

**Route**: `/admin/channels/messaging/templates/new` or `/admin/channels/messaging/templates/{id}`  
**Purpose**: Create/edit WhatsApp or SMS template  
**Permission**: `channels.messaging.templates.manage`  

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Internal identifier (ar/en) |
| Type | enum | Yes (on create) | No | WhatsApp, SMS |
| Language | enum | Yes (on create) | No | en, ar (separate template per language) |
| Subject | string | No | Yes | Subject line (email-style, optional) |
| Body | textarea | Yes | Yes | Message body (ar/en), can include {{variable}} placeholders |

**Variables** (clickable tags to insert):
- `{{ticket.reference}}`, `{{ticket.subject}}`, `{{customer.name}}`, `{{agent.name}}`, etc.

**Character Counter**:
- SMS: "160 chars left" (SMS limit ~160 chars per message; longer messages split)
- WhatsApp: "1024 chars left" (WhatsApp templates up to ~1024 chars)

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST/PATCH template | `POST /messaging/templates` or `PATCH /messaging/templates/{id}` | (same) | No |
| Test | Modal to preview with sample variables | (no API call) | (same) | N/A |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /messaging/templates`, `PATCH /messaging/templates/{id}`, `GET /messaging/templates`

---

## Live Chat Console

**Route**: `/crm/channels/live-chat` or `/channels/chat` (agent view)  
**Purpose**: Monitor, accept, and handle live chat sessions  
**Permission**: `channels.chat.view` + `channels.chat.accept`/`channels.chat.transfer`  

**Layout** (Two-column or card-based):

**Left: Session Queue**
- Queued sessions (number badge showing count)
  - Session ID, visitor name (optional), queue time
  - [Accept] button (per session)
- Active sessions (assigned to me or team)
  - Session ID, visitor name, assigned agent, elapsed time
  - [Transfer] button (if `channels.chat.transfer`)
  - [End] button (if `channels.chat.manage`)

**Right: Chat Transcript** (when session selected):
- Visitor messages + agent replies (transcript view)
- Visitor typing indicator ("Visitor is typing…")
- Message input field + [Send] button
- [Transfer to Agent] dropdown (if applicable)
- [Transfer to Bot/Queue] button (if applicable)
- [End Session] button

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Accept Session | POST accept | `POST /channels/chat/sessions/{session}/accept` | `channels.chat.accept` | Yes |
| Send Message | POST message | `POST /channels/chat/sessions/{session}/messages` | `channels.chat.view` | No (needs Idempotency-Key) |
| Transfer to Agent | Modal to select agent | `POST /channels/chat/sessions/{session}/transfer` | `channels.chat.transfer` | Yes |
| Transfer to Queue | Back to queue | (implicit) | (same) | N/A |
| End Session | POST end | `POST /channels/chat/sessions/{session}/end` | (same) | Yes |

**Related endpoints**:
- `POST /channels/chat/sessions` (start session — public, no auth needed)
- `GET /channels/chat/sessions` (list queued/active)
- `GET /channels/chat/sessions/{session}/messages` (transcript)
- `POST /channels/chat/sessions/{session}/accept`, `/transfer`, `/end`

**Notes**:
- Queue length config: max 20 sessions waiting
- Concurrency per agent: max 3 active sessions
- Reconnect window: 300s (if visitor closes tab and returns within 5 min, reconnect to same session)
- Abandon timeout: 900s (after 15 min of no agent reply, session auto-closes)

---

## Navigation Map

- **Sidebar: Admin > Channels**
  - **Web Forms** → (list) → (click) → **Form Editor** (fields builder)
    - **View Submissions** → (submissions log)
  - **Email Inbound** → (log) → (inspect/replay actions)
  - **Message Templates** → (list) → (click) → **Template Editor**
- **Sidebar: Channels > Live Chat** → **Chat Console** (queue + active sessions)

---

**Next**: [07-knowledge.md](07-knowledge.md)
