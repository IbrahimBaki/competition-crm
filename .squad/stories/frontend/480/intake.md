> **Fetched from azure:** [480](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/480)  
> *Fetched 2026-08-27T18:56:55.035Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-03 - Ticket List and Ticket Detail Workspace  
**Type:** User Story  
**Status:** New  
**Labels:** AgentWorkspace, Frontend, P4-Frontend, SupportCRM, Tickets

### Description

As a Support Agent, I want one screen per ticket showing the conversation, the customer context, the SLA position and every action I can take, plus a fast filterable list to work from, so that I can clear a queue without switching tools.
The largest and most valuable frontend story: this is the screen agents live in.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/480/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `480` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `AgentWorkspace, Frontend, P4-Frontend, SupportCRM, Tickets`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-03 - Ticket List and Ticket Detail Workspace
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want one screen per ticket showing the conversation, the customer context, the SLA position and every action I can take, plus a fast filterable list to work from, so that I can clear a queue without switching tools.
The largest and most valuable frontend story: this is the screen agents live in.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
The ticket list supports filters, sort, saved views, pagination and bulk actions, and stays responsive on a realistic dataset. 

Ticket detail shows conversation, properties, customer context, SLA position and history on one screen without a second navigation. 

Replying and adding an internal note are visually unmistakable; it must be impossible to send an internal note to a customer by accident. 

Only transitions the server reports as available are offered; an invalid transition is never presented. 

Assignment conflicts and stale edits surface the conflict clearly with a path to recover, rather than failing silently. 

SLA state is displayed from the API's own figures and is never recalculated in the browser. 

Attachment upload shows progress, the scanning state and rejection reasons.
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
