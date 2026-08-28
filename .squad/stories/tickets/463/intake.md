> **Fetched from azure:** [463](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/463)  
> *Fetched 2026-08-26T20:40:20.162Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-16 - Assignment, Queues, Ownership Transfer and Concurrent-Work Protection  
**Type:** User Story  
**Status:** New  
**Labels:** AgentWorkspace, Backend, P1-Core, SupportCRM, Tickets

### Description

As a Support Manager, I want tickets to have a clear owner, visible queues, controlled transfer between agents and departments, and protection against two agents working the same ticket at once, so that no request is either orphaned or answered twice.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/tickets/463/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `tickets`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `463` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `AgentWorkspace, Backend, P1-Core, SupportCRM, Tickets`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-16 - Assignment, Queues, Ownership Transfer and Concurrent-Work Protection
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager, I want tickets to have a clear owner, visible queues, controlled transfer between agents and departments, and protection against two agents working the same ticket at once, so that no request is either orphaned or answered twice.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
A ticket can be assigned by a manager, claimed by an agent, transferred between agents, and transferred between departments, each gated by its own permission key. 

Department and personal queues are queryable with the standard collection contract. 

Claiming an already-claimed ticket returns a distinct conflict code rather than silently overwriting. 

Concurrent edits use optimistic concurrency: a stale write returns a conflict code carrying the current state. 

A ticket transferred to another department is re-evaluated against that department's routing and SLA. 

Every assignment change is recorded in ticket history with the actor and the previous owner.
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
