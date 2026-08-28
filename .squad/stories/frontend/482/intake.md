> **Fetched from azure:** [482](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/482)  
> *Fetched 2026-08-27T18:57:50.546Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-05 - Agent Workspace: Queues, Tasks, Reminders, Quick Replies and Collaboration  
**Type:** User Story  
**Status:** New  
**Labels:** AgentWorkspace, Frontend, P4-Frontend, SupportCRM

### Description

As a Support Agent, I want my day assembled on one landing screen — what is assigned to me, what is at risk, what I owe someone — so that I start work without hunting for it.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/482/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `482` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `AgentWorkspace, Frontend, P4-Frontend, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-05 - Agent Workspace: Queues, Tasks, Reminders, Quick Replies and Collaboration
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want my day assembled on one landing screen — what is assigned to me, what is at risk, what I owe someone — so that I start work without hunting for it.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
The landing screen shows assigned tickets, department queue, items approaching SLA breach, and overdue tasks. 

Tasks and reminders can be created, completed and rescheduled, from a ticket or standalone. 

Quick replies are insertable with placeholders resolved, and are available in both languages. 

Mentions and watchers are usable from the ticket, and notifications route the user back to the right place. 

Availability status is settable and visibly affects routing eligibility. 

The screen is usable on a tablet and readable on a phone.
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
