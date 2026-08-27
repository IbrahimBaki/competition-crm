# 02. Organization

**Domains**: Organisation  
**Surface**: Staff CRM only  
**Permissions**: `org.branches.*`, `org.departments.*`, `org.teams.*`, `admin.structure.manage`

---

## Organization Structure Screen

**Route**: `/admin/organization/structure`  
**Purpose**: Manage branches, departments, and teams hierarchy; assign users  
**Permission**: `admin.structure.manage`  

**Hierarchical Tree View**:
```
Branches (root level)
├─ Branch 1 (ar: "الفرع الأول")
│  ├─ Department 1A
│  │  ├─ Team 1A-I
│  │  └─ Team 1A-II
│  ├─ Department 1B
│  └─ [+ Add Department]
├─ Branch 2
└─ [+ Add Branch]
```

**Actions** (on each tree node):

### Branch Level
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal/navigate to branch edit | `/admin/org/branches/{id}` | (same) | N/A |
| Activate/Deactivate | Toggle active flag | `POST /branches/{branch}/activate` or `/deactivate` | (same) | Yes |
| Delete | Modal confirm + DELETE | `DELETE /branches/{branch}` | (same) | Yes (only if no departments) |
| Add Department | Link to create department form | `/admin/org/departments/new?branch={branch_id}` | (same) | N/A |

### Department Level
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal/navigate to department edit | `/admin/org/departments/{id}` | (same) | N/A |
| Activate/Deactivate | Toggle | `POST /departments/{department}/activate` or `/deactivate` | (same) | Yes |
| Delete | DELETE | `DELETE /departments/{department}` | (same) | Yes (only if no teams) |
| Add Team | Link to create team form | `/admin/org/teams/new?department={department_id}` | (same) | N/A |

### Team Level
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal/navigate to team edit | `/admin/org/teams/{id}` | (same) | N/A |
| Activate/Deactivate | Toggle | `POST /teams/{team}/activate` or `/deactivate` | (same) | Yes |
| Delete | DELETE | `DELETE /teams/{team}` | (same) | Yes (only if no users assigned) |

**Related endpoints**:
- `GET /branches`, `GET /departments`, `GET /teams` (fetch tree structure)
- `POST /branches/{branch}/activate|deactivate`, `POST /departments/{department}/activate|deactivate`, `POST /teams/{team}/activate|deactivate`
- `DELETE /branches/{branch}`, `DELETE /departments/{department}`, `DELETE /teams/{team}`

**Notes**:
- Tree is read-only by default; edit actions open modals/separate screens
- Drag-to-reorder: not required (use position column in DB if ordered by admin)
- Deactivated branches/departments/teams: grayed out, cannot assign new users to them, but existing assignments remain

---

## Branch Edit Screen

**Route**: `/admin/org/branches/new` (create) or `/admin/org/branches/{id}` (edit)  
**Purpose**: Create/edit branch + configure working hours and holidays  
**Permission**: `admin.structure.manage`  

**Section 1: Basic Info**

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Branch label (ar/en) |
| Code | string | Yes | No | Unique identifier (e.g., "SAU-RYD" for Riyadh) |
| Timezone | select | Yes | No | IANA timezone (e.g., "Asia/Riyadh") |
| Is 24/7 | checkbox | No | No | If checked, SLA targets run continuously; otherwise respects working hours |
| Is Active | checkbox | No | No | Deactivate to prevent new assignments |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST/PATCH | `POST /branches` or `PATCH /branches/{branch}` | (same) | No |
| Cancel | Go back | — | N/A | N/A |

---

**Section 2: Working Hours**

**Purpose**: Define weekly schedule (e.g., 9 AM–6 PM, Sat–Thu)  

**Week Grid** (clickable, drag-to-fill):
```
         Monday  Tuesday  Wednesday  Thursday  Friday  Saturday  Sunday
Start:    09:00   09:00    09:00    09:00     OFF     09:00    OFF
End:      18:00   18:00    18:00    18:00     —       18:00    —
Break:    12:00-13:00 (optional)
```

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | PUT working hours | `PUT /branches/{branch}/working-hours` | (same) | Yes |

**Related endpoints**:
- `GET /branches/{branch}/working-hours` (fetch current)
- `PUT /branches/{branch}/working-hours` (save)

**Notes**:
- Used by SLA engine to calculate business-time countdowns
- OFF = day is a non-working day (no hours work)
- Optional break times (lunch) can be configured per day
- Changes apply immediately (affects SLA calculations going forward)

---

**Section 3: Holidays**

**Purpose**: Define non-working days (Eid, National Day, etc.)  

**Table**:
| Date | Name (ar/en) | Actions |
|---|---|---|
| 2026-09-23 | National Day / يوم الوطني | Edit / Delete |
| 2026-10-15 | Eid Al-Fitr / عيد الفطر | — |
| [+ Add Holiday] | — | — |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Add Holiday | Navigate/modal to create | `/admin/org/branches/{branch}/holidays/new` | (same) | N/A |
| Edit | Navigate/modal to edit | `/admin/org/branches/{branch}/holidays/{id}` | (same) | N/A |
| Delete | DELETE | `DELETE /branches/{branch}/holidays/{holiday}` | (same) | Yes |

**Holiday Add/Edit Modal**:
**Fields**:
| Field | Type | Required | Bilingual |
|---|---|---|---|
| Date | date | Yes | No |
| Name | string | Yes | Yes |

**Related endpoints**:
- `GET /branches/{branch}/holidays` (list)
- `POST /branches/{branch}/holidays` (create)
- `PUT /branches/{branch}/holidays/{holiday}` (update)
- `DELETE /branches/{branch}/holidays/{holiday}` (delete)

**Notes**:
- Holidays must be bilingual (e.g., "Eid Al-Fitr" + "عيد الفطر")
- Multiple holidays on same date: not allowed (unique constraint)
- Used by SLA engine: days marked as holiday skip working-time calculations

---

## Department Edit Screen

**Route**: `/admin/org/departments/new` or `/admin/org/departments/{id}`  
**Purpose**: Create/edit department  
**Permission**: `admin.structure.manage`  

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Department label (ar/en) |
| Branch | select | Yes | No | Parent branch (set on create, cannot change) |
| Routing Strategy | enum | Yes | No | manual, round_robin, least_busy, skill_based (affects auto-assignment) |
| Is Active | checkbox | No | No | Deactivate to prevent new assignments |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST/PATCH | `POST /departments` or `PATCH /departments/{department}` | (same) | No |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /departments`, `PATCH /departments/{department}`

**Notes**:
- Routing Strategy: used by automation rule engine to assign tickets to agents
  - `manual`: no auto-assign (agent must manually claim)
  - `round_robin`: cycle through department agents evenly
  - `least_busy`: assign to agent with fewest open tickets
  - `skill_based`: assign based on agent tags/skills (not yet fully implemented)

---

## Team Edit Screen

**Route**: `/admin/org/teams/new` or `/admin/org/teams/{id}`  
**Purpose**: Create/edit team  
**Permission**: `admin.structure.manage`  

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Team label (ar/en) |
| Department | select | Yes | No | Parent department (set on create) |
| Is Active | checkbox | No | No | Deactivate |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST/PATCH | `POST /teams` or `PATCH /teams/{team}` | (same) | No |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /teams`, `PATCH /teams/{team}`

**Notes**:
- Teams are optional organizational units (not required by SLA or ticketing logic)
- Useful for visual grouping in queue/reporting views

---

## User Branch & Department Assignment Screen

**Route**: `/admin/users/{user}/assignments` (accessible from user edit screen)  
**Purpose**: Assign/reassign user to branches and departments  
**Permission**: `admin.users.manage`  

**Section 1: Branch Assignment**

**Assigned Branches** (table):
| Branch | Primary | Actions |
|---|---|---|
| Riyadh | ☑ (radio) | Remove |
| Jeddah | ○ | Remove |
| [+ Add Branch] | — | — |

**Add Branch Modal**:
- Select from unassigned branches
- Checkbox "Set as primary" (only one primary)
- Save

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Add Branch | Modal to select + save | `POST /users/{user}/branches/{branch}` | (same) | Yes |
| Set Primary | Radio button or button | `POST /users/{user}/branches/{branch}/primary` | (same) | Yes |
| Remove | DELETE | `DELETE /users/{user}/branches/{branch}` | (same) | Yes |

**Related endpoints**:
- `POST /users/{user}/branches/{branch}` (attach)
- `POST /users/{user}/branches/{branch}/primary` (set primary)
- `DELETE /users/{user}/branches/{branch}` (detach)

---

**Section 2: Department Assignment**

**Assigned Departments** (table):
| Branch | Department | Actions |
|---|---|---|
| Riyadh | Support | Remove |
| Riyadh | Billing | Remove |
| [+ Add Department] | — | — |

**Add Department Modal**:
- Filter by branch (if user assigned to multiple branches)
- Select department(s)
- Save (can assign multiple departments)

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Add Department | Modal + save | `POST /users/{user}/departments/{department}` | (same) | Yes |
| Remove | DELETE | `DELETE /users/{user}/departments/{department}` | (same) | Yes |

**Related endpoints**:
- `POST /users/{user}/departments/{department}` (attach)
- `DELETE /users/{user}/departments/{department}` (detach)

**Notes**:
- Primary branch: used for default timezone when displaying SLA times (ticket SLA uses ticket's department's branch's timezone, or user's primary branch as fallback)
- Departments: optional; user can be assigned to multiple departments across multiple branches
- If user has no department assignments, they can see all department queues (subject to permission keys like `tickets.view.department`)

---

## Navigation Map

- **Sidebar: Admin > Organization** → **Structure Screen** (tree view, actions)
  - **Branch** (click) → **Branch Edit** → (tabs: Basic, Working Hours, Holidays)
  - **Department** (click) → **Department Edit**
  - **Team** (click) → **Team Edit**
- **Sidebar: Admin > Users** → (from user list/edit) → **User Assignments** (Branch/Department assignment)

---

**Next**: [03-customers.md](03-customers.md)
