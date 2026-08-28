# 12. Customer Portal

> **Backend availability — read before building.**
> The portal account is **read-only**: only `GET /portal/me` exists. There is no
> profile update, no portal notification-preferences endpoint and no portal
> password-change endpoint. (`/notifications/preferences` is a staff route and
> is blocked for portal tokens.) Build these screens only after the endpoints land.

**Domains**: Portal  
**Surface**: Customer Self-Service Only  
**Auth**: `portalToken` (separate from staff `bearer` token)

---

## Portal App Shell

**Layout** (simplified, lightweight):
```
┌──────────────────────────────────────┐
│ Logo      |  My Account ▼  | Logout  │  ← Top Nav (minimal)
├──────────────────────────────────────┤
│                                      │
│  Main Content (no sidebar)           │
│  ┌────────────────────────────────┐  │
│  │ My Tickets / Ticket Detail     │  │
│  │ Reply Composer / Feedback      │  │
│  │ Account Settings               │  │
│  └────────────────────────────────┘  │
│                                      │
└──────────────────────────────────────┘
```

**Top Nav**:
- **Logo** (left): back to my-tickets home
- **My Account** (right): profile, settings, logout
- **No search, no admin features, no notifications bell** — customer-focused only

---

## Portal Register Screen

**Route**: `/portal/register` (public, pre-auth)  
**Purpose**: Customer creates account  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Email | string | Yes | Will receive verification code |
| Password | string | Yes | Min 8 chars |
| Confirm Password | string | Yes | Must match |
| Full Name | string | Yes | Display name |
| Preferred Language | enum | Yes | en, ar (default: org default) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create Account | POST register | `POST /portal/auth/register` | (none) | No |

**Validation**:
- Email unique (not already registered)
- Password strength (8+ chars, mix of upper/lower/numbers/symbols recommended)

**States**:
- **Success**: "Account created. Please verify your email." → redirect to verify screen
- **Error**: field validation errors from API

**Related endpoints**:
- `POST /portal/auth/register`

---

## Portal Email Verification Screen

**Route**: `/portal/verify` (post-register, pre-login)  
**Purpose**: Verify email address via code  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Verification Code | string | Yes | 6-digit code from email |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Verify | POST verify | `POST /portal/auth/verify` (with email + code) | (none) | No |
| Resend Code | POST resend | (similar endpoint) | (none) | Yes |

**States**:
- **Valid code**: "Email verified. You can now log in." → redirect to login
- **Invalid/expired code**: "Invalid code" error, show [Resend Code] button
- **Success**: Account now active, ready to login

**Related endpoints**:
- `POST /portal/auth/verify`

---

## Portal Login Screen

**Route**: `/portal/login` (public, pre-auth)  
**Purpose**: Authenticate customer account  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Email | string | Yes | Account email |
| Password | string | Yes | Account password |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Log In | POST login | `POST /portal/auth/login` | (none) | No |

**States**:
- **Loading**: button disabled, spinner
- **Error**: "Invalid email or password" (generic, don't leak whether account exists)
- **Success**: set `portalToken`, redirect to my-tickets

**Additional Options**:
- [Forgot Password] link → password reset flow
- [Create Account] link → register

**Related endpoints**:
- `POST /portal/auth/login` (returns `portalToken`)

**Note**:
- No 2FA for customers (kept simple)
- Account lockout after 5 failed logins in 15 minutes

---

## My Tickets Screen

**Route**: `/portal/tickets` (post-login)  
**Purpose**: View and manage own tickets  
**Permission**: Implicit (any logged-in customer)  

**List**:
| Reference | Subject | Status | Created | Last Update | Actions |
|---|---|---|---|---|---|
| TKT-042 | Cannot reset password | Open | 2d ago | 2h ago | View |
| TKT-041 | Billing inquiry | Closed | 5d ago | 3d ago | View |
| TKT-040 | Feature request | Resolved | 1w ago | 1w ago | View |

**Columns**:
- **Reference**: ticket ID (TKT-xxx)
- **Subject**: ticket subject
- **Status**: Open, Pending, Resolved, Closed (badge-styled)
- **Created**: when ticket was created (relative time)
- **Last Update**: when last message posted (relative time)
- **Actions**: [View] → navigate to ticket detail

**Filters** (simple):
- Status: All (default), Open, Closed
- Date range: This week, This month, All time

**Sorting**:
- By created date (newest first), status, last updated

**Empty State** (if no tickets):
- "You don't have any tickets. [Create Ticket] or [Contact Us] to start."

**Related endpoints**:
- `GET /portal/tickets` (list customer's own tickets only)

---

## Portal Ticket Detail Screen

**Route**: `/portal/tickets/{id}`  
**Purpose**: View ticket details and reply  
**Visibility**: Customer sees ONLY their own ticket (404 if not owner)  

**Header**:
- **Reference** (TKT-042)
- **Subject** (editable by staff only, read-only for customer)
- **Status** badge (Open/Resolved/etc.)
- **Created** timestamp
- **Last Updated** timestamp

**Conversation Thread** (read-only for staff messages):
- Each message shows: timestamp, author name, body, attachments
- **Internal notes from staff**: NOT VISIBLE to customer (filter client-side per `visibility: internal` flag)
- Customer's own replies: fully visible, marked as "You"
- Staff replies: marked as "Support Team" or agent name
- Attachments: thumbnails (clickable to download), with status badges (pending/clean/infected)

**Reply Composer** (if ticket is open):
```
[Text input] [Attach File] [Send]
Max 5000 chars | Drag to upload (Max 10MB)
```

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Message | textarea | Yes | Plain text only (no HTML/markdown) |
| Attachments | file upload | No | Max 5 files, 10 MB each |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Send Reply | POST message | `POST /portal/tickets/{ticket}/messages` | (implicit) | No (needs Idempotency-Key) |

**States** (compose area):
- **Closed ticket**: "This ticket is closed. You cannot reply." (disabled composer)
- **Open**: composer active
- **Sending**: button disabled, spinner
- **Error**: field validation errors or general error toast

**Related endpoints**:
- `GET /portal/tickets/{ticket}` (detail, customer's own ticket only)
- `GET /portal/tickets/{ticket}/messages` (conversation thread, filtered to exclude internal notes)
- `POST /portal/tickets/{ticket}/messages` (send reply with `visibility: public`)

**Notes**:
- Replies always public (visibility: public) — no internal notes for customers
- If ticket has been resolved/closed for 14 days, show feedback survey instead of composer (see below)

---

## Feedback Survey Screen

**Route**: Shown on ticket detail when ticket is closed/resolved + feedback due  
**Purpose**: Collect customer satisfaction after issue resolved  

**Display**:
```
How satisfied are you with our support?

[⭐] [⭐⭐] [⭐⭐⭐] [⭐⭐⭐⭐] [⭐⭐⭐⭐⭐]

Optional comment:
[Text area]

[Submit Feedback]
```

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Rating | stars (1–5) | Yes | Click star to rate |
| Comment | textarea | No | Additional feedback (max 1000 chars) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Submit Feedback | POST feedback | `POST /portal/tickets/{ticket}/feedback` (or via ticket submission) | (implicit) | Yes |

**States**:
- **Before rating**: both buttons disabled
- **After rating**: submit button enabled
- **Sending**: button disabled
- **Success**: "Thank you for your feedback!" message, hide survey

**Related endpoints**:
- `POST /portal/tickets/{ticket}/feedback` (with rating + optional comment)

**Notes**:
- Feedback collected post-resolution (after 14-day reopen window closes)
- Staff can see feedback results in ticket detail (permission: `tickets.feedback.view`)

---

## Portal Account Settings

**Route**: `/portal/account` or `/portal/settings`  
**Purpose**: Manage customer account preferences  
**Permission**: Implicit (any logged-in customer)  

**Sections**:

### Profile
**Fields** (editable):
- **Name** | Read-only, or editable (TBD)
- **Email** | Read-only (cannot change email via portal, contact support)
- **Preferred Language** | Dropdown (en, ar) — affects UI + notifications
- **Phone** | Optional, for contact channel

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | PATCH profile | `PATCH /portal/me` ⚠️ **NOT IMPLEMENTED** (or similar) | (implicit) | No |

### Notification Preferences
**Checkboxes** (which notifications to receive via email):
- ☑ Ticket updates (when staff replies)
- ☑ Feedback requests (when ready to rate)
- ☐ Marketing emails (unsubscribe option)

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save | PATCH preferences | `PATCH /portal/notifications/preferences` ⚠️ **NOT IMPLEMENTED** or similar | (implicit) | No |

### Change Password
**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Current Password | string | Yes | Verification |
| New Password | string | Yes | Min 8 chars |
| Confirm New Password | string | Yes | Must match |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Change Password | PATCH password | (endpoint TBD, likely PATCH /portal/password) | (implicit) | No |

### Account Deletion (optional)
**Button**:
- [Delete My Account] → confirm modal → anonymizes user + account

**Related endpoints**:
- `GET /portal/me` (fetch current profile)
- `PATCH /portal/me` ⚠️ **NOT IMPLEMENTED** (update profile)
- (Password change endpoint TBD)

---

## Portal Guest Ticket Tracking (Public, No Auth)

**Route**: `/portal/guest/tickets/{trackingToken}` (public, no login)  
**Purpose**: Track ticket status without creating account  
**Auth**: None (token-based access)  

**Access**:
- Unique token issued when ticket created (e.g., from web form submission)
- Shared via email: "Track your ticket: https://crm.example.com/portal/guest/tickets/{token}"

**Display** (read-only):
- **Reference**: TKT-042
- **Subject**: Cannot reset password
- **Status**: Open (badge)
- **Created**: 2 days ago
- **Last Update**: 2 hours ago
- **Message**: "Your ticket is being reviewed by our support team. Thank you for your patience."

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| None | (view-only) | — | (none) | N/A |

**Related endpoints**:
- `GET /portal/guest/tickets/{token}` (fetch ticket by token, no auth required)

**Notes**:
- Token access persists for ticket lifetime + 30 days after closure (for final feedback)
- No reply capability via guest tracking (encourages account creation for ongoing support)

---

## Portal Guest Feedback (Public, No Auth)

**Route**: `/portal/guest/feedback/{trackingToken}` (public, no login)  
**Purpose**: Collect feedback from guest tracking link  

**Display**: Same as feedback survey (see above), but without login requirement

**Related endpoints**:
- `POST /portal/guest/feedback/{token}` (submit feedback anonymously via token)

---

## Navigation Map

- **Unauthenticated Flow**:
  - **Login** → (enter email/password) → **My Tickets**
  - **Register** → (create account) → **Email Verify** → **Login** → **My Tickets**
  - **Forgot Password** → reset flow (via email) → **Login**
- **Authenticated Flow**:
  - **My Tickets** → (list) → (click ticket) → **Ticket Detail** (conversation + reply/feedback)
  - **My Account** (top-right menu) → **Settings** (profile, notifications, password, logout)
- **Public Guest Flow**:
  - Email link: `/portal/guest/tickets/{token}` → **Ticket Detail** (view-only) or **Feedback Form**

---

**Next**: [13-public-surfaces.md](13-public-surfaces.md)
