> **Fetched from azure:** [467](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/467)  
> *Fetched 2026-08-26T23:26:01.153Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-20 - Agent Workspace APIs: Tasks, Reminders, Quick Replies, Mentions and Watchers  
**Type:** User Story  
**Status:** New  
**Labels:** AgentWorkspace, Backend, P1-Core, SupportCRM

### Description

As a Support Agent, I want the supporting tools of daily work exposed as APIs — my tasks and reminders, reusable replies, pulling a colleague in, and following a ticket I do not own — so that the workspace screen has everything it needs from one contract.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/notifications/467/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `notifications`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `467` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `AgentWorkspace, Backend, P1-Core, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-20 - Agent Workspace APIs: Tasks, Reminders, Quick Replies, Mentions and Watchers
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want the supporting tools of daily work exposed as APIs — my tasks and reminders, reusable replies, pulling a colleague in, and following a ticket I do not own — so that the workspace screen has everything it needs from one contract.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Tasks and reminders have an owner, optional ticket link, due instant, state and history; overdue items are queryable. 

Reminders fire through the shared notification service, honouring the branch calendar where the reminder is expressed in working time. 

Quick replies exist in personal and shared scopes, are bilingual, and support placeholders (customer name, ticket reference, agent name) resolved server-side. 

An agent can mention a colleague on an internal note; the mention notifies and grants visibility consistent with permissions. 

Any permitted user can watch or unwatch a ticket without owning it, and watchers receive configured updates. 

None of these surfaces leak internal notes or watched tickets to customer-facing endpoints.
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
