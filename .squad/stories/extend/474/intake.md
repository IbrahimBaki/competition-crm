> **Fetched from azure:** [474](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/474)  
> *Fetched 2026-08-27T09:51:50.587Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-27 - Customer Portal APIs: Authentication, Guest Tracking, Visibility Allowlist and Feedback  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, CSAT, CustomerPortal, P3-Extended, SupportCRM

### Description

As a Customer, I want to submit and follow my own requests, read published help content and rate the service, without ever being able to see anything internal, so that self-service reduces contact volume without creating a data leak.
The portal is a separate guard, separate prefix and separate resources on purpose: it should be structurally impossible for an internal field to reach it.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/extend/474/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `extend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `474` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, CSAT, CustomerPortal, P3-Extended, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-27 - Customer Portal APIs: Authentication, Guest Tracking, Visibility Allowlist and Feedback
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Customer, I want to submit and follow my own requests, read published help content and rate the service, without ever being able to see anything internal, so that self-service reduces contact volume without creating a data leak.
The portal is a separate guard, separate prefix and separate resources on purpose: it should be structurally impossible for an internal field to reach it.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Portal authentication uses its own guard and session; a portal session can never call a staff endpoint and a staff session can never act as a customer. 

Registration verifies the contact identity before it is linked to an existing customer record. 

A guest who submitted through a public form can track that one request through a scoped, expiring link without an account. 

Portal responses are built from an explicit field allowlist: a new internal field added later is not automatically serialised, and internal notes and internal attachments are unreachable even by identifier. 

Altering a reference or identifier in a request never returns another customer's record. 

Feedback is a 1-5 score plus optional comment, requested at a defined point after resolution, submittable once per ticket via an expiring link, and stored against the correct ticket.
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
