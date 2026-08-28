> **Fetched from azure:** [448](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/448)  
> *Fetched 2026-08-26T10:20:09.352Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-01 - Organization Structure: Branches, Departments, Teams and User Placement  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Administration, Backend, Blocker, P0-Foundation, SupportCRM

### Description

As a System Administrator, I want to model the company as branches, departments and teams and place each staff user inside that structure, so that every later feature (routing, SLA, reporting scope, permissions) has a stable organisational spine to hang on.
Scope: single organisation. No tenant provisioning, no subscriptions, no cross-organisation isolation.
This is the first story in the project because ticket routing, SLA calendars and report scoping are all meaningless without it.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/448/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `448` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Administration, Backend, Blocker, P0-Foundation, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-01 - Organization Structure: Branches, Departments, Teams and User Placement
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want to model the company as branches, departments and teams and place each staff user inside that structure, so that every later feature (routing, SLA, reporting scope, permissions) has a stable organisational spine to hang on.
Scope: single organisation. No tenant provisioning, no subscriptions, no cross-organisation isolation.
This is the first story in the project because ticket routing, SLA calendars and report scoping are all meaningless without it.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Branch, department and team can be created, renamed, activated and deactivated through the API. 

A department belongs to exactly one branch; a team belongs to exactly one department. 

A user can be assigned to one or more departments and to one or more branches, with one primary branch. 

Deactivating a department is blocked, or requires reassignment, while open tickets still reference it. 

Every structure name is stored bilingually (ar/en) and returned as an object, not a pre-picked string. 

Structure changes are written to the audit trail with actor, before and after values.
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
