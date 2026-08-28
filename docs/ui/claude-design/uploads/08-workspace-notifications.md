# 08. Workspace & Notifications

**Domains**: Workspace, Notifications  
**Surface**: Staff CRM only  
**Permissions**: `workspace.*`, `notifications.*`

---

## My Tasks Screen

**Route**: `/crm/workspace/tasks` or `/tasks`  
**Purpose**: Personal task list (to-do items, reminders)  
**Permission**: `workspace.tasks.view.own` (for own tasks)  

**Tabs**:
- **Open** (default, shows active tasks)
- **Done** (completed tasks, swipe to archive)
- **Others' Tasks** (if `workspace.tasks.view.others`)

**List** (Open tab):

| Task | Assigned To | Due In | Priority | Linked Ticket | Actions |
|---|---|---|---|---|---|
| Review customer feedback | Me | 2h 30m | Normal | — | [Open] [Complete] [Remind Later] |
| Follow up on TKT-042 | Me | Tomorrow | High | TKT-042 | — |
| Department meeting prep | Me | 3 days | Normal | — | — |

**Filters**:
- Due date (today, this week, overdue, all)
- Assigned to (me, team, department, all) — based on permissions
- Priority (all, high, normal, low)
- Linked ticket (optional)

**Sorting**:
- Due date (ascending), priority, created date

**Quick Actions** (per row):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Complete | Toggle state to done | `POST /agent-tasks/{task}/state` (state=done) | `workspace.tasks.manage.own` | Yes |
| Snooze | Reschedule reminder | Modal to pick new time | (same) | N/A |
| Delete | DELETE | `DELETE /agent-tasks/{task}` | (same) | Yes |

**Create Task Button**:

**Create Task Modal**:
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Title | string | Yes | Task description |
| Description | textarea | No | Additional details |
| Due At | datetime | No | Date + time picker |
| Due In Working Time | checkbox | No | If checked, due time respects branch working hours |
| Priority | enum | No | low, normal, high (default: normal) |
| Linked Ticket | select (autocomplete) | No | Optional ticket to link |
| Assign To | select | No | If permission `workspace.tasks.manage.others`, can assign to other agents |
| Reminder | enum | No | at_due_time, 15_min_before, 1_hour_before, 1_day_before |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create | POST task | `POST /agent-tasks` | `workspace.tasks.manage.own` | No |

**Related endpoints**:
- `GET /agent-tasks` (list with filter)
- `POST /agent-tasks` (create)
- `PATCH /agent-tasks/{task}` (update)
- `POST /agent-tasks/{task}/state` (change state)
- `DELETE /agent-tasks/{task}` (delete)

**Notes**:
- Task state machine: open → done → archived
- Completed tasks move to Done tab, can be archived (cleared from done tab)
- Due time can respect working hours (WorkingTimeService) or wall-clock time (checkbox)
- Reminders sent via notification system (in-app bell, or email if opted in)

---

## Quick Replies Manager

**Route**: `/crm/workspace/quick-replies`  
**Purpose**: Manage canned response templates (personal + shared)  
**Permission**: `workspace.quick_replies.view` (view), `workspace.quick_replies.manage` (create/edit/delete)  

**Tabs**:
- **My Quick Replies** (personal, only I can use)
- **Shared** (department or org-wide, all staff can use)

**List** (My Quick Replies tab):

| Title | Category | Body Preview | Department (if shared) | Actions |
|---|---|---|---|---|
| Thanks for your patience | General | "Thank you for reaching out…" | — | Edit / Delete |
| Password reset steps | Technical | "1. Click forgot password…" | Support | — |

**Filters**:
- Department (my dept, all depts, specific dept) — visible if any shared replies exist

**Actions** (per reply):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal/navigate | `/crm/workspace/quick-replies/{id}` | `workspace.quick_replies.manage` | N/A |
| Delete | DELETE | `DELETE /quick-replies/{reply}` | (same) | Yes |

**Create/Edit Quick Reply Modal**:
**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Title | string | Yes | Yes | Short label (ar/en) |
| Body | textarea | Yes | Yes | Response template (ar/en), can include {{ticket.reference}}, {{customer.name}} variables |
| Scope | enum | No | No | personal (only me), shared (if `workspace.quick_replies.manage`) |
| Department (if shared) | select | If scope=shared | No | Make available to dept staff |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST/PATCH | `POST /quick-replies` or `PATCH /quick-replies/{reply}` | (same) | No |
| Test Render | Modal to preview with sample variables | (no API call) | (same) | N/A |

**Related endpoints**:
- `GET /quick-replies` (list, scope filtered)
- `POST /quick-replies`, `PATCH /quick-replies/{reply}`, `DELETE /quick-replies/{reply}`
- `POST /quick-replies/render` (preview with variable substitution)

**Notes**:
- Quick replies insertable in ticket reply composer (type "/" or [Insert] button)
- Variables: {{ticket.reference}}, {{ticket.subject}}, {{customer.name}}, {{agent.name}}, {{current_date}}, etc.

---

## Notification Inbox

**Route**: `/notifications` or `/crm/notifications`  
**Purpose**: In-app notification center  
**Permission**: `notifications.view.own`  

**Notification Bell** (in top nav):
- Shows unread count badge
- Click to open dropdown (recent notifications)

**Full Inbox Page** (click "View All" in dropdown):

**List** (newest first):
| Timestamp | Icon | Message | Read | Actions |
|---|---|---|---|---|
| 2m ago | 🎫 | "Your ticket TKT-042 was escalated" | — | [Read] |
| 15m ago | 👤 | "Ahmed assigned ticket TKT-041 to you" | ✓ | — |
| 2h ago | ⏰ | "SLA warning: TKT-039 approaching deadline" | ✓ | — |

**Filters**:
- Date range (today, this week, all)
- Type (all, tickets, assignments, sla_warnings, mentions, etc.)
- Read status (unread, read, all)

**Bulk Actions**:
- [ ] Select multiple
- **Mark as Read**
- **Delete**

**Actions** (per notification):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Mark as Read | PATCH notification | `POST /notifications/{notification}/read` | (same) | Yes |
| Delete | DELETE | `DELETE /notifications/{notification}` | (same) | Yes |
| Go to Source | Navigate to ticket/resource | (navigation) | (same) | N/A |

**Mark All as Read**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Mark All Read | PATCH all | `POST /notifications/read-all` | (same) | Yes |

**Related endpoints**:
- `GET /notifications` (list with filter/sort)
- `POST /notifications/{notification}/read` (mark one as read)
- `POST /notifications/read-all` (mark all as read)

**Notes**:
- Retention: 180 days (older notifications auto-deleted)
- Notification events: sla.warning, sla.breach, ticket.assigned, ticket.transferred, ticket.escalated, ticket.message.posted, user.invited, agent_task.reminder, ticket.mentioned, ticket.watched.update, web_form.submission.acknowledged

---

## Notification Preferences

**Route**: `/settings/notifications` or accessible from notification bell [Preferences]  
**Purpose**: Configure which notifications to receive + delivery channels  
**Permission**: `notifications.manage_preferences`  

**Event × Channel Matrix**:

```
Event Type             | In-App | Email
─────────────────────────────────────
SLA Warning            | ☑      | ☑
SLA Breach             | ☑      | ☑
Ticket Assigned        | ☑      | ☐
Ticket Transferred     | ☑      | ☑
Ticket Escalated       | ☑      | ☑
Ticket Message Posted  | ☑      | ☑
User Invited           | ☑      | ☑
Agent Task Reminder    | ☑      | ☐
Ticket Mentioned       | ☑      | ☑
Ticket Watched Update  | ☑      | ☐
Form Submission Ack    | ☑      | ☐
```

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Toggle Preference | PATCH preference | `PUT /notifications/preferences` (send full matrix) | (same) | Yes |

**Related endpoints**:
- `GET /notifications/preferences` (fetch current matrix)
- `PUT /notifications/preferences` (update preferences)

**Notes**:
- Default preferences set per org (from backend config)
- User can override all preferences
- In-app notifications always available (not deletable from system perspective, only from inbox)

---

## Notification Delivery Log (Admin)

**Route**: `/admin/notifications/deliveries`  
**Purpose**: Debug notification delivery failures  
**Permission**: `notifications.view_delivery_log`  

**Table** (failed deliveries only):

| Timestamp | Event | Recipient | Channel | Status | Error | Retry | Actions |
|---|---|---|---|---|---|---|---|
| 2026-08-27 10:30 | ticket.assigned | Ahmed Ali | email | failed | SMTP timeout | 1/5 | [Retry] [Details] |
| 2026-08-27 09:45 | sla.warning | Sarah Smith | email | failed | Unsubscribed | 0/5 | [Details] |

**Filters**:
- Event type, recipient (user search), channel, status (failed, suppressed, pending)
- Date range
- Error type

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Retry Delivery | POST retry | (internal, likely DELETE + re-send) | (same) | Yes (idempotent by design) |
| View Details | Modal showing full event + error | (no API call) | (same) | N/A |

**Related endpoints**:
- `GET /notifications/deliveries/failed` (list failed deliveries)

**Notes**:
- Useful for troubleshooting why users aren't getting notifications
- Retry button: manually re-queue delivery

---

## Navigation Map

- **Sidebar: Workspace**
  - **My Tasks** → (list, filters)
    - [Create Task] → **Create Task Modal**
    - (click task) → inline edit or [Complete] / [Delete]
  - **Quick Replies** → (my personal + shared org)
    - (click reply) → **Edit Modal** or inline actions
    - [Create] → **Create Modal**
  - **Notifications** → (inbox, filters)
    - (in top nav) → Notification Bell → dropdown + [View All]
    - [Preferences] → **Notification Preferences Matrix**
- **Sidebar: Admin > Notifications**
  - **Delivery Log** → (failed deliveries, retry actions)

---

**Next**: [09-ai-assistance.md](09-ai-assistance.md)
