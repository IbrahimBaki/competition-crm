> **Fetched from azure:** [460](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/460)  
> *Fetched 2026-08-26T18:28:26.650Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-13 - Ticket Core: Creation, Classification, Custom Fields, Tags, Search and History  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, P1-Core, SupportCRM, Tickets

### Description

As a Support Agent, I want to create a ticket for a customer, classify it, find it later, and see everything that has ever happened to it, so that a request is a tracked object rather than a message in someone's inbox.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/tickets/460/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `tickets`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `460` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, P1-Core, SupportCRM, Tickets`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-13 - Ticket Core: Creation, Classification, Custom Fields, Tags, Search and History
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want to create a ticket for a customer, classify it, find it later, and see everything that has ever happened to it, so that a request is a tracked object rather than a message in someone's inbox.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
A ticket always has a customer, a department and a priority; it may be unassigned. 

Each ticket receives a unique, never-reused reference in a defined format. 

Categories form a configurable tree up to three levels, bilingual; a category may carry additional custom fields captured at creation. 

Free-form tags can be applied and filtered on. 

The ticket list supports the standard collection contract plus saved views, and returns counts in meta. 

Ticket search covers subject, body and reference, using the same Arabic normalisation as customer search. 

Every meaningful change writes a history event (actor, what changed, when); history is a first-class table, not derived from updated_at.
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
