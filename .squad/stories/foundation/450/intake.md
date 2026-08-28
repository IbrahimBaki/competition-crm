> **Fetched from azure:** [450](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/450)  
> *Fetched 2026-08-26T11:25:42.952Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-03 - Permission Key Catalog, Roles and Data Scope Enforcement  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Blocker, P0-Foundation, Security, SupportCRM

### Description

As a System Administrator, I want an explicit catalogue of permission keys, roles built from those keys, and a scope model (own / team / department / any), so that every endpoint in the system authorises against a stable key instead of a role name.
Roles are editable by the administrator; permission keys are fixed and belong to the code. Any check written as a role-name comparison is a defect, because renaming a role would silently change security.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/450/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `450` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Blocker, P0-Foundation, Security, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-03 - Permission Key Catalog, Roles and Data Scope Enforcement
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want an explicit catalogue of permission keys, roles built from those keys, and a scope model (own / team / department / any), so that every endpoint in the system authorises against a stable key instead of a role name.
Roles are editable by the administrator; permission keys are fixed and belong to the code. Any check written as a role-name comparison is a defect, because renaming a role would silently change security.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
A published catalogue of permission keys exists in the format {module}.{action}[.{scope}], with scope in own, team, department, any. 

Roles are created and edited by combining keys; the administrator role cannot have its own administration keys removed. 

Authorisation is enforced by a Policy checked against a permission key; a request-level authorize() alone is not accepted. 

No production code path compares a role name; a static check or test proves this. 

Scope is enforced on data, not only on the action: a department-scoped user listing tickets receives only their departments' tickets. 

GET /auth/me returns the effective permission key list, never the role name, so the client can build its UI from keys.
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
