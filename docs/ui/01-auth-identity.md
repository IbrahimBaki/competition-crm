# 01. Auth & Identity

> **Backend availability — read before building.**
> There is **no direct user CRUD**. Users are created by invitation
> (`POST /users/invite`) and managed with `POST /users/{user}/activate` /
> `deactivate`. There is no create, update or fetch-by-id route for users —
> no POST or PATCH on the users collection, and no users/{id} detail route.
> The create/edit form below must be built as an *invite* form plus the
> placement endpoints (`/users/{user}/branches/...`, `/users/{user}/departments/...`).

**Domains**: Security  
**Surface**: Staff CRM only  
**Permissions**: `admin.users.*`, `admin.roles.manage`, `admin.audit.view`, `dataprotection.*`

---

## Login Screen

**Route**: `/login` (pre-auth, redirect to dashboard if already logged in)  
**Purpose**: Authenticate staff member via email/password + optional 2FA  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Email | string | Yes | Validated email format |
| Password | string | Yes | Min 8 chars |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Sign In | POST login | `POST /auth/login` | (none) | No |
| Forgot Password? | Link to password reset | `/password-reset` | (none) | N/A |

**States**:
- **Empty**: form ready, focus on email
- **Loading**: button disabled, spinner
- **Error**: show field error (invalid credentials → "Email or password incorrect") + toast
- **2FA Required**: hidden, trigger 2FA challenge screen overlay
- **Success**: set auth token, redirect to dashboard

**Related endpoints**:
- `POST /auth/login` → returns `bearer` token + user object with permission_keys

**Notes**:
- Email is case-insensitive
- Failed login counter: after 5 failures in 15 minutes, account locked for 15 minutes
- Throttled: `throttle:public` + bot protection (CAPTCHA after 3 failures from same IP)

---

## 2FA Challenge Screen

**Route**: `/auth/2fa` (modal over login, after credentials validated)  
**Purpose**: Verify one-time code from authenticator app  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Code | string | Yes | 6-digit numeric (auto-focus, auto-submit when filled) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Verify | POST verify | `POST /auth/two-factor/challenge` | (none) | No |
| Use Recovery Code | Link to recovery-code input | (toggle) | (none) | N/A |

**States**:
- **Normal**: code input focused
- **Recovery Mode**: switch to recovery code input (8-character code, single-use, must be consumed)
- **Error**: "Invalid code" or "Recovery code already used" toast
- **Success**: set token, redirect to dashboard

**Related endpoints**:
- `POST /auth/two-factor/challenge` → returns `bearer` token

**Notes**:
- TOTP: 30-second window, auto-submit when 6 digits entered
- Recovery codes: single-use, each use deletes it from the system
- If user loses access to authenticator, they can request admin to reset 2FA

---

## Password Reset Flow

### Request Page
**Route**: `/password-reset` (pre-auth)  
**Purpose**: Request password reset email  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Email | string | Yes | Staff email |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Send Reset Email | POST request | `POST /auth/password/forgot` | (none) | Yes (idempotent) |

**States**:
- **Empty**: form ready
- **Loading**: button disabled
- **Success**: "Check your email for a reset link" message (even if email not found, for security)
- **Error**: generic error toast

**Related endpoints**:
- `POST /auth/password/forgot`

### Reset Page
**Route**: `/password-reset/{token}` (pre-auth, token from email link)  
**Purpose**: Set new password  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Password | string | Yes | Min 8 chars, must differ from old password |
| Confirm Password | string | Yes | Must match password field |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Reset Password | POST reset | `POST /auth/password/reset` | (none) | No |

**States**:
- **Loading**: button disabled, spinner
- **Validation error**: show password requirements, field errors
- **Token expired**: "Reset link expired; request a new one" + link back to request page
- **Success**: "Password updated; you can now log in" + redirect to login

**Related endpoints**:
- `POST /auth/password/reset` (with token in body)

---

## Accept Invitation Screen

**Route**: `/invitations/{token}/accept` (pre-auth)  
**Purpose**: New staff member accepts invitation, sets password, opts into 2FA  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Email | string | No | Pre-filled, read-only (from token) |
| Name | string | Yes | Full name |
| Password | string | Yes | Min 8 chars |
| Confirm Password | string | Yes | Must match password |
| Enable 2FA | checkbox | No | If checked, show TOTP setup (QR code, manual key entry, recovery codes) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Accept & Continue | POST accept | `POST /invitations/{token}/accept` | (none) | No |

**States**:
- **2FA Setup**: if checkbox enabled, show QR code + recovery codes (save/screenshot warning)
- **Loading**: button disabled
- **Token expired**: "Invitation expired; ask admin for a new one"
- **Email already registered**: "This account already exists; try logging in"
- **Success**: "Account created; redirecting to login" + redirect to login

**Related endpoints**:
- `POST /invitations/{token}/accept`

---

## User List Screen

**Route**: `/admin/users`  
**Purpose**: View and manage all staff members  
**Permission**: `admin.users.manage`  

**Fields**:
| Field | Type | Bilingual | Enum | Notes |
|---|---|---|---|---|
| Name | string | No | — | Sortable |
| Email | string | No | — | Sortable, filterable |
| Status | enum | No | active, deactivated | Filterable badge |
| 2FA | enum | No | enabled, disabled, no_recovery_codes | Icon indicator |
| Last Login | datetime | No | — | Sortable, relative time (e.g., "2 days ago") |
| Locked Until | datetime | No | — | If account is locked (too many failed logins) |

**Actions** (per row):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to user edit | `/admin/users/{id}` | (same) | N/A |
| Activate | POST activate | `POST /users/{user}/activate` | (same) | Yes |
| Deactivate | POST deactivate | `POST /users/{user}/deactivate` | (same) | Yes |
| Unlock | POST unlock | (internal, sets lockout timer to now) | (same) | Yes |
| Reset 2FA | Modal to reset | (internal, clear 2FA columns) | (same) | Yes |
| Erase Personal Data | Modal confirm + POST erase | `POST /users/{user}/erase-personal-data` | (same) | Yes |

**List Controls**:
- **Pagination**: page, per_page (default 25)
- **Sort**: by name, email, status, last_login (click column header)
- **Filter**: by status (active/deactivated), locked status, 2FA enabled/disabled

**Related endpoints**:
- `GET /users` (list with filtering/sorting/pagination)
- `GET /users/{id}` ⚠️ **NOT IMPLEMENTED** (detail, not exposed as separate screen, used on edit)
- `POST /users/{user}/activate`, `POST /users/{user}/deactivate`
- `POST /users/{user}/erase-personal-data`

**Notes**:
- Deactivated users cannot log in but retain audit history and associated tickets
- 2FA reset: only admin can do this; user must re-enable 2FA on next login
- Personal data erasure: anonymizes user record (name → "Anonymized User", email → UUID), preserves audit trail

---

## User Create/Edit/Detail Screen

**Route**: `/admin/users/new` (create) or `/admin/users/{id}` (edit)  
**Purpose**: Create new user or edit existing user  
**Permission**: `admin.users.manage`  

**Fields**:
| Field | Type | Required | Bilingual | Enum | Notes |
|---|---|---|---|---|
| Name | string | Yes | No | — | Full name |
| Email | string | Yes | No | — | Unique, staff email |
| Status | enum | No (auto active) | No | active, deactivated | On create: always active; edit: toggle via buttons |
| Locale | enum | No (default en) | No | en, ar | User's preferred language |
| Branches | multi-select | Yes | No | — | List of branches this user can access; first in list is "primary" |
| Departments | multi-select | No | No | — | Departments within assigned branches (optional, for department-scoped permissions) |
| Roles | multi-select | Yes | No | — | Which roles to assign (e.g., Agent, Manager) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST create/PATCH update | `POST /users` ⚠️ **NOT IMPLEMENTED** or `PATCH /users/{user}` ⚠️ **NOT IMPLEMENTED** | (same) | No |
| Invite User | POST invite | `POST /users/invite` (instead of save if password not set) | (same) | No |
| Cancel | Go back | — | N/A | N/A |

**States**:
- **Create mode**: name/email/locale/branches/roles required; no status field (always active); invite method chosen (set password vs send invite email)
- **Edit mode**: can deactivate user (via separate action buttons), can reset 2FA, can reassign branches/departments/roles
- **Validation errors**: field-level errors from API
- **Success**: toast "User created/updated", redirect to list

**Related endpoints**:
- `POST /users` ⚠️ **NOT IMPLEMENTED** (create)
- `PATCH /users/{user}` ⚠️ **NOT IMPLEMENTED** (update)
- `POST /users/invite` (send invite email if password not initially set)
- `POST /users/{user}/branches/{branch}` (attach to branch)
- `DELETE /users/{user}/branches/{branch}` (detach)
- `POST /users/{user}/branches/{branch}/primary` (set primary branch)
- `POST /users/{user}/departments/{department}` (attach to department)
- `DELETE /users/{user}/departments/{department}` (detach)

**Notes**:
- On create with invite: email goes out with accept-invitation token link (user sets password themselves)
- On create with password: admin sets password upfront; user can change it on first login
- Primary branch: used for default timezone/working hours when displaying SLA times
- Branch/department assignment is separate from roles; a user can be assigned to multiple branches and departments, but only one is "primary"

---

## Role List Screen

**Route**: `/admin/roles`  
**Purpose**: View and manage roles + permission mappings  
**Permission**: `admin.roles.manage`  

**Fields**:
| Field | Type | Bilingual | Notes |
|---|---|---|---|
| Display Name | string | Yes | Role label (e.g., "مدير النظام" / "System Administrator") |
| Is System | boolean | No | If true, role is built-in (cannot delete, can edit display_name only) |
| Permissions | count | No | Number of assigned permission keys |

**Actions** (per row):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to role edit | `/admin/roles/{id}` | (same) | N/A |
| Delete | Modal confirm + DELETE | `DELETE /roles/{role}` | (same) | Yes (only custom roles) |

**Related endpoints**:
- `GET /roles` (list)

**Notes**:
- System roles (Administrator, Manager, Supervisor, Agent, Viewer): cannot delete, only rename
- Custom roles: can create, rename, delete

---

## Role Create/Edit Screen

**Route**: `/admin/roles/new` or `/admin/roles/{id}`  
**Purpose**: Create or edit role + assign permission keys  
**Permission**: `admin.roles.manage`  

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|
| Display Name | string | Yes | Yes | Bilingual label (ar/en) |

**Permissions Matrix**:
- Fetch from `GET /permissions/catalogue` — returns ~90 permission keys grouped by module (admin, org, tickets, attachments, dataprotection, customers, sla, automation, notifications, workspace, channels, knowledge, ai, reports, integrations)
- Display as collapsible sections per module
- Each permission key shows: key, description, scope (if applicable: own/team/department/any as radio buttons)
- Checkboxes to assign/revoke per role

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | POST create / PATCH update | `POST /roles` or `PATCH /roles/{role}` | (same) | No |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `GET /permissions/catalogue` (fetch permission key catalogue)
- `POST /roles` (create)
- `PATCH /roles/{role}` (update display_name only)
- (Permission assignment happens via PUT on role with role+permission body, exact endpoint TBD in final OpenAPI)

**Notes**:
- Scope radios (own/team/department/any) visible only for permission keys that support scopes (most view/manage keys)
- Default: no scope selected = permission not granted; select one scope to grant
- Permission catalogue includes description for each key (e.g., "View tickets in user's own queue only" for `tickets.view.own`)

---

## Audit Log Screen

**Route**: `/admin/audit-logs`  
**Purpose**: View append-only audit trail of sensitive changes  
**Permission**: `admin.audit.view`  

**Fields**:
| Field | Type | Sortable | Filterable | Notes |
|---|---|---|---|---|
| Timestamp | datetime | Yes | Yes (date range picker) | UTC + branch timezone |
| Actor | string | No | Yes (user search autocomplete) | Staff member who made the change |
| Action | string | Yes | Yes (enum: user_activated, user_deactivated, user_role_assigned, user_role_revoked, permission_changed, customer_blocked, ticket_status_changed, ticket_assigned, sla_policy_updated, automation_rule_executed, etc.) | Human-readable action label |
| Target | string | No | Yes | Resource type + ID (e.g., "User: Ahmed Ali", "Ticket: TKT-001") |
| Before/After | JSON | No | No | Shown on expand (side-by-side diff view) |
| Request ID | UUID | No | Yes | For tracing request through logs |

**Actions** (per row):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Expand Details | Show before/after JSON | (no API call) | (same) | N/A |

**List Controls**:
- **Pagination**: page, per_page (default 50, max 100)
- **Sort**: by timestamp (desc default), action, actor
- **Filter**: date range (today, last 7 days, last 30 days, custom), actor, action, target resource type

**Related endpoints**:
- `GET /audit-logs` (list with filtering/sorting/pagination)

**Notes**:
- Audit is append-only — no delete/edit UI ever exists
- Retention: 2555 days minimum (per config)
- Useful for compliance audits, incident investigation, permission tracking

---

## Data Protection Screen

**Route**: `/admin/data-protection`  
**Purpose**: View retention policy, manage user erasure, access backup/restore status  
**Permission**: `dataprotection.view`  

**Tabs**:

### Tab 1: Retention Policy
**Purpose**: View current retention config per data type  

**Read-only table**:
| Data Type | Retention Days | Notes |
|---|---|---|
| Tickets & Messages | 2555 (7 years) | After retention period, auto-deleted |
| Attachments | 1095 (3 years) | — |
| Audit Logs | 2555 min, 365 min override | — |
| Chat Transcripts | 90 | — |
| Provider Raw Payloads (email/WhatsApp/SMS) | 90 | — |
| Webhook Deliveries | 30 | — |
| Import Runs | 90 | — |

**Actions**: None (read-only, managed by admin config)

### Tab 2: Personal Data Erasure
**Purpose**: Anonymize staff user records (name → "Anonymized", email → UUID)  
**Permission**: `dataprotection.execute_erasure`  

**Fields**:
| Field | Type | Notes |
|---|---|---|
| User to Erase | select (autocomplete) | Search staff by name/email |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Request Erasure | POST erase | `POST /users/{user}/erase-personal-data` | (same) | Yes |

**States**:
- **Modal**: confirm "Erase personal data for {name}? This cannot be undone. Audit trail will remain."
- **Success**: toast "User data erased", user now appears as "Anonymized User" in audit logs
- **Error**: show error reason (e.g., user already anonymised)

**Related endpoints**:
- `POST /users/{user}/erase-personal-data`

**Notes**:
- Only touches user record (name, email, 2FA secrets); does NOT delete tickets/messages/audit entries
- Useful for GDPR right-to-be-forgotten requests

### Tab 3: Backup & Restore Status
**Purpose**: View backup schedule, last backup, restore options  
**Permission**: `dataprotection.view`  

**Read-only display**:
- Last backup: date/time, size, status (success/failed)
- Scheduled backups: frequency (daily), next run time
- Manual restore: (button to contact support / describe restore process — exact endpoint TBD)

---

## My Account Settings Screen (Personal)

**Route**: `/account` or `/admin/profile` (accessible by any logged-in user, not just admins)  
**Purpose**: Logged-in user can update own password, email, locale, 2FA  

**Fields**:
| Field | Type | Editable | Notes |
|---|---|---|---|
| Name | string | No | Read-only |
| Email | string | No | Read-only (cannot change own email via API) |
| Locale | enum | Yes | en, ar — persist to user record |

**Sections**:

### Change Password
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Current Password | string | Yes | For verification |
| New Password | string | Yes | Min 8 chars, must differ from current |
| Confirm New Password | string | Yes | Must match new password |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Change Password | PATCH password | (internal endpoint, exact TBD) | (none) | No |

### 2FA Management
**Display**:
- **If 2FA disabled**: "Two-factor authentication is off. [Enable 2FA]" button
- **If 2FA enabled**: "Two-factor authentication is active. [Regenerate Recovery Codes] [Disable 2FA]" button

**Enable 2FA Modal**:
- Show QR code + manual key entry field
- "Scan with authenticator app" instructions
- Verify code input (6 digits, auto-submit)
- Display recovery codes (save/screenshot warning)

**Regenerate Recovery Codes Modal**:
- Confirm "Generate new recovery codes? Old ones will be invalidated."
- Display new codes

**Disable 2FA Modal**:
- Confirm "Disable 2FA? Your account will be less secure."
- Require current password confirmation

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Enable | POST enable | `POST /auth/two-factor` | (none) | No |
| Confirm (after scan) | POST confirm | `POST /auth/two-factor/confirm` | (none) | No |
| Regenerate Codes | POST regen | `POST /auth/two-factor/recovery-codes` | (none) | No |
| Disable | DELETE disable | `DELETE /auth/two-factor` | (none) | Yes |

**Related endpoints**:
- Password change: (TBD, likely PATCH /auth/password or similar)
- `POST /auth/two-factor`, `POST /auth/two-factor/confirm`, `POST /auth/two-factor/recovery-codes`, `DELETE /auth/two-factor`
- `GET /auth/me` (fetch current user + permissions on page load)

**Notes**:
- Locale change is instant (affects subsequent API responses' language)
- 2FA can only be managed by the user themselves (no admin-force-disable here, that's in user management)
- Recovery codes: single-use, each use removes it from system

---

## Navigation Map

- **Login** → (if 2FA enabled) → **2FA Challenge** → **Dashboard**
- **Forgot Password** → **Password Reset Request** → (email link) → **Password Reset** → **Login**
- **Invitation Link** → **Accept Invitation** → (if 2FA enabled in form) → **2FA Setup** → **Login**
- **Sidebar: Admin** → Users & Roles section:
  - **Users** → (list) → (click user) → **User Detail/Edit** → (optional sub-actions: unlock, reset 2FA, erase)
  - **Roles** → (list) → (click role) → **Role Edit (Permission Matrix)** → save
  - **Audit Logs** → (filtered list)
  - **Data Protection** → (tabs: retention, erasure, backup status)
- **Top Nav: User Profile** → **My Account Settings** → (change password, manage 2FA, set locale)

---

**Next**: [02-organization.md](02-organization.md)
