> **Fetched from azure:** [466](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/466)  
> *Fetched 2026-08-26T22:41:45.520Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-19 - Shared Notification Service: Events, Bilingual Templates, User Preferences and Delivery Tracking  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Automation, Backend, Notifications, P1-Core, SupportCRM

### Description

As a Backend Developer, I want notifications to be one shared service consuming domain events, so that SLA warnings, assignments, mentions, escalations and portal updates all use the same templates, preferences and delivery tracking instead of each feature inventing its own emails.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/notifications/466/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `notifications`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `466` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Automation, Backend, Notifications, P1-Core, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-19 - Shared Notification Service: Events, Bilingual Templates, User Preferences and Delivery Tracking
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Backend Developer, I want notifications to be one shared service consuming domain events, so that SLA warnings, assignments, mentions, escalations and portal updates all use the same templates, preferences and delivery tracking instead of each feature inventing its own emails.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Notifications are triggered by domain events dispatched after commit, not by controllers. 

Templates are bilingual, stored as data, and rendered in the recipient's locale with the correct direction. 

Each user controls which events notify them and on which channels; a defined default applies to new users. 

Delivery is attempted asynchronously with retry and backoff; permanent failures are recorded and visible. 

Notification content respects authorisation: a recipient never receives data they could not query. 

A notification is not sent twice for the same event and recipient, even if the job is retried.
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
