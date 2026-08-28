> **Fetched from azure:** [475](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/475)  
> *Fetched 2026-08-27T10:46:59.434Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-28 - Reporting and Dashboard APIs: Scoped Data, Consistent SLA Figures, Exports and Scheduling  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, P3-Extended, Reporting, SupportCRM

### Description

As a Support Manager, I want reports that agree with the ticket-level data, respect who is asking, and can be exported or scheduled, so that management decisions rest on numbers nobody has to argue about.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/extend/475/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `extend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `475` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, P3-Extended, Reporting, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-28 - Reporting and Dashboard APIs: Scoped Data, Consistent SLA Figures, Exports and Scheduling
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager, I want reports that agree with the ticket-level data, respect who is asking, and can be exported or scheduled, so that management decisions rest on numbers nobody has to argue about.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
The report set covers ticket volume and trend, SLA performance, agent performance, satisfaction, backlog and aging, and a management dashboard. 

All reports share one filter contract (date range, branch, department, team, agent, category, priority, channel, tag). 

Results are limited to what the caller is permitted to see; an agent sees their own performance, a manager their departments. 

SLA figures are read from recorded SLA state, not recalculated in the report layer, and reconcile exactly with the ticket detail. 

Date filters are evaluated consistently against stored UTC instants and presented in the requesting context's zone. 

Export to CSV, Excel and PDF is supported; large exports run in the background and are delivered rather than blocking the request. 

A report can be scheduled for recurring delivery to named recipients.
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
