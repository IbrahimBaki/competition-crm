> **Fetched from azure:** [451](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/451)  
> *Fetched 2026-08-26T12:05:12.681Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-04 - Staff Authentication, Session Security, Two-Factor and User Lifecycle  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Blocker, P0-Foundation, Security, SupportCRM

### Description

As a System Administrator, I want staff users to be invited, authenticated and deactivated safely, with session rules and optional two-factor authentication, so that access to customer data is controlled from the first day rather than hardened later.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/451/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `451` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Blocker, P0-Foundation, Security, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-04 - Staff Authentication, Session Security, Two-Factor and User Lifecycle
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want staff users to be invited, authenticated and deactivated safely, with session rules and optional two-factor authentication, so that access to customer data is controlled from the first day rather than hardened later.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Invite flow: an administrator invites by email; the user sets their own password from a single-use, expiring link. 

Password policy, failed-attempt lockout and password reset are enforced server-side. 

Two-factor authentication (TOTP) can be enabled per user and required organisation-wide by policy, with recovery codes. 

Session cookies are HttpOnly, Secure, SameSite=Lax, with CSRF protection on every mutating request. 

Deactivating a user immediately invalidates their sessions and tokens; their historical actions remain attributed to them. 

Login, logout, failed login, 2FA enrolment and deactivation are all audited.
```

---

## Attachments

Place files in `attachments/` next to this `intake.md`, then list them here so the planner knows what to open.

| File (relative to this folder) | What it is |
| ------------------------------ | ---------- |
| *(e.g. `attachments/flow.png`)* | *(e.g. UX flow)* |

*(Add rows per file. If none, write "None.")*

---

## Dependencies

- **Blocked by / related ids:** (tracker ids only; optional short note)
- **Depends on code areas or other stories:**

## Extra notes (optional)

- Anything not captured above (e.g. chat context) — keep short.

## Technical hints (optional)

- APIs, screens, services already discussed. Repos/roots: `.`. Primary language: `php`.

## Out of scope

- What this story explicitly does **not** cover:
