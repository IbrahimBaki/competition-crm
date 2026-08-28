> **Fetched from azure:** [481](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/481)  
> *Fetched 2026-08-27T18:57:39.887Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-04 - Customer Screens and the 360 View  
**Type:** User Story  
**Status:** New  
**Labels:** Customers, Frontend, P4-Frontend, SupportCRM

### Description

As a Support Agent, I want to find a customer quickly and see who they are and everything that has happened with them, so that I have context before I answer.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/481/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `481` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Customers, Frontend, P4-Frontend, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-04 - Customer Screens and the 360 View
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want to find a customer quickly and see who they are and everything that has happened with them, so that I have context before I answer.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Customer search returns results fast and matches Arabic name variants and any contact identity. 

The profile shows identity, contact methods, company account and tier, notes and attachments. 

The 360 timeline merges tickets and messages across channels in one chronological, paginated feed. 

Duplicate candidates are reviewable and merge is initiable from the UI, with a clear preview of what will move. 

Blocking, unblocking and anonymising are permission-gated and require confirmation stating the consequence. 

The screen degrades correctly when the ERP context panel is unavailable.
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
