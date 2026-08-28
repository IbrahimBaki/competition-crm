> **Fetched from azure:** [454](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/454)  
> *Fetched 2026-08-26T15:38:21.871Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-07 - Day-One Seed Data and Environment Bootstrap  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Administration, Backend, Blocker, P0-Foundation, SupportCRM

### Description

As a Developer, I want a single seeding command that produces a fully operable organisation, so that a fresh database is a working system rather than an empty shell that every developer fills differently.
One default status or three ad-hoc roles is under-specification, not simplification: the rest of the backlog assumes the full catalogue exists.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/454/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `454` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Administration, Backend, Blocker, P0-Foundation, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-07 - Day-One Seed Data and Environment Bootstrap
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Developer, I want a single seeding command that produces a fully operable organisation, so that a fresh database is a working system rather than an empty shell that every developer fills differently.
One default status or three ad-hoc roles is under-specification, not simplification: the rest of the backlog assumes the full catalogue exists.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Seeding installs the full permission key catalogue and the standard roles. 

Seeding installs the complete status catalogue with lifecycle types, the priority set, and the category tree, all bilingual. 

Seeding installs at least a standard branch calendar and a 24/7 calendar, plus the default SLA policy and its targets per priority. 

Seeding installs the bilingual notification and email templates referenced by later stories. 

Seeding is idempotent: running it twice does not duplicate rows or reset customised values. 

A separate demo seeder produces realistic sample customers and tickets for manual testing, and is never run in production.
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
