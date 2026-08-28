> **Fetched from azure:** [455](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/455)  
> *Fetched 2026-08-26T15:58:17.905Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-08 - Business Audit Trail and Operational Observability  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, Operations, P0-Foundation, Security, SupportCRM

### Description

As a System Administrator and as an Operator, I want two distinct records — an immutable business audit trail for who changed what, and structured operational logs with correlation IDs for diagnosing failures — so that both compliance questions and production incidents can be answered.
They are deliberately separate: an audit entry is evidence, a log line is diagnostics.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/455/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `455` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, Operations, P0-Foundation, Security, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-08 - Business Audit Trail and Operational Observability
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator and as an Operator, I want two distinct records — an immutable business audit trail for who changed what, and structured operational logs with correlation IDs for diagnosing failures — so that both compliance questions and production incidents can be answered.
They are deliberately separate: an audit entry is evidence, a log line is diagnostics.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Meaningful business changes (security, permissions, ticket ownership, status, SLA resets, customer data) write an audit entry with actor, action, target, before/after and timestamp. 

Audit entries cannot be edited or deleted through any application path, including by an administrator. 

The audit log is searchable and filterable by actor, target, action and date range, gated by a permission key. 

Every request carries a correlation/request ID that appears in logs, in error responses and in queued jobs spawned by that request. 

Structured logs never contain credentials, tokens or full personal data. 

Health endpoints report application, database, queue and mail status; a failing dependency is visible without reading logs.
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
