> **Fetched from azure:** [461](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/461)  
> *Fetched 2026-08-26T19:19:09.303Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-14 - Ticket Lifecycle: Status Catalogue, Allowed Transitions, Reopen, Spam, Merge, Split and Link  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, P1-Core, SupportCRM, Tickets

### Description

As a Support Manager, I want the set of statuses, the transitions permitted between them, and the structural operations on tickets to be defined and enforced by the server, so that the lifecycle cannot be bypassed by a client and reporting is based on states that actually mean something.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/tickets/461/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `tickets`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `461` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, P1-Core, SupportCRM, Tickets`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-14 - Ticket Lifecycle: Status Catalogue, Allowed Transitions, Reopen, Spam, Merge, Split and Link
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager, I want the set of statuses, the transitions permitted between them, and the structural operations on tickets to be defined and enforced by the server, so that the lifecycle cannot be bypassed by a client and reporting is based on states that actually mean something.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Each status maps to one of the fixed lifecycle types; administrators may rename a status but cannot invent a new type. 

Whether a status stops an SLA clock is derived from its lifecycle type, not set independently. 

The allowed transition map is enforced server-side; an illegal transition returns a distinct error code, and each ticket response exposes its currently available transitions. 

Resolved tickets can be reopened within a configured window; after that they close permanently. 

Marking spam removes the ticket from operational reporting without deleting it, and restoring is permission-gated. 

Tickets can be merged, split and linked; merging preserves both conversations and both histories and is audited. 

Every transition writes a history event including the actor and, where required, a reason.
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
