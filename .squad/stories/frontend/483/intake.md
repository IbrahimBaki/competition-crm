> **Fetched from azure:** [483](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/483)  
> *Fetched 2026-08-27T18:58:03.037Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-06 - Administration and Configuration Screens  
**Type:** User Story  
**Status:** New  
**Labels:** Administration, Frontend, P4-Frontend, SupportCRM

### Description

As a System Administrator, I want to configure the system without a developer — structure, calendars, users, roles, catalogues, SLA policies, rules, templates, channels and branding — so that operational change does not require a release.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/483/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `483` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Administration, Frontend, P4-Frontend, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-06 - Administration and Configuration Screens
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want to configure the system without a developer — structure, calendars, users, roles, catalogues, SLA policies, rules, templates, channels and branding — so that operational change does not require a release.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Branches, departments and teams are manageable, including deactivation with its guard rails. 

Working hours, weekend pattern, holidays and time zone are editable per branch, with a preview of the resulting working week. 

Users are invitable, editable and deactivatable; roles are composable from permission keys with the keys described in plain language. 

Statuses, priorities, categories and custom fields are editable bilingually, with prohibited changes blocked rather than failing on save. 

SLA policies, targets, assignment strategies, escalation levels and automation rules are configurable, with the rule execution log visible. 

Channel configuration shows connection health and lets an operator replay failed inbound messages. 

Every destructive action states its consequence and requires confirmation.
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
