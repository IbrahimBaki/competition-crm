> **Fetched from azure:** [457](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/457)  
> *Fetched 2026-08-26T16:59:18.132Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-10 - Backup, Restore, Data Retention and Personal Data Erasure  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, Compliance, Operations, P0-Foundation, SupportCRM

### Description

As a System Administrator, I want scheduled backups with a tested restore, defined retention periods, and a supported way to erase an individual's personal data on request, so that data protection obligations are designed in rather than retrofitted.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/457/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `457` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, Compliance, Operations, P0-Foundation, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-10 - Backup, Restore, Data Retention and Personal Data Erasure
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want scheduled backups with a tested restore, defined retention periods, and a supported way to erase an individual's personal data on request, so that data protection obligations are designed in rather than retrofitted.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Automated backups run on a schedule, are stored separately from the application, and are verified. 

A restore has actually been performed into a scratch environment and the procedure is documented with a measured recovery time. 

Retention periods are configured per data class (tickets, messages, attachments, logs, audit) and enforced by a scheduled purge. 

A customer can be anonymised: personal identifiers are irreversibly replaced while ticket volumes, SLA history and reporting aggregates remain intact. 

An erasure or purge action is itself recorded in the audit trail. 

Audit records are exempt from purge within their own retention window and are never silently dropped.
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
