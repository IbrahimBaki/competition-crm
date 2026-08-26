> **Fetched from azure:** [465](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/465)  
> *Fetched 2026-08-26T22:02:05.176Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-18 - Rule Engine: Automatic Assignment, Escalation Levels and Scheduled Automations  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Automation, Backend, P1-Core, SLA, SupportCRM

### Description

As a System Administrator, I want one configurable trigger-condition-action rule engine that covers automatic assignment, escalation and time-based automations, so that operational policy is configuration rather than code, and every automated action can be explained afterwards.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/sla/465/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `sla`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `465` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Automation, Backend, P1-Core, SLA, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-18 - Rule Engine: Automatic Assignment, Escalation Levels and Scheduled Automations
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want one configurable trigger-condition-action rule engine that covers automatic assignment, escalation and time-based automations, so that operational policy is configuration rather than code, and every automated action can be explained afterwards.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Routing strategies are configurable per department (manual, round-robin, least-busy, skill-based) with defined behaviour when no agent is available. 

Escalation is explicit: levels, trigger conditions, target (person, role or department) and outcome (reassign, raise priority, notify, or a combination) are all configured, not implied. 

Manual escalation by an agent is supported, requires a reason, and follows the same path as automatic escalation. 

Time-based rules run on a schedule (approaching breach, stale ticket, auto-close after inactivity) and are idempotent under repeated runs. 

Rule evaluation order and conflict resolution are deterministic and documented. 

Every rule execution writes an execution log entry explaining which rule fired, why, and what it changed; the same explanation appears in ticket history.
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
