> **Fetched from azure:** [477](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/477)  
> *Fetched 2026-08-27T11:17:56.726Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-30 - Contract Freeze, Performance Budget and Backend Definition of Done  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** API, Backend, Gate, P3-Extended, SupportCRM

### Description

As a Tech Lead, I want the API contract frozen, a measured performance budget, and an explicit definition of done for the backend phase, so that frontend work starts against a stable target rather than a moving one.
This is the gate between the backend phase and the frontend phase. It is the only story in the backlog whose output is a decision rather than a feature.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/extend/477/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `extend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `477` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `API, Backend, Gate, P3-Extended, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-30 - Contract Freeze, Performance Budget and Backend Definition of Done
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Tech Lead, I want the API contract frozen, a measured performance budget, and an explicit definition of done for the backend phase, so that frontend work starts against a stable target rather than a moving one.
This is the gate between the backend phase and the frontend phase. It is the only story in the backlog whose output is a decision rather than a feature.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
The OpenAPI document covers every implemented endpoint, validates in CI, and generates a client without manual patching. 

A runnable request collection exercises the primary flows end to end against a seeded database. 

Measured response-time budgets are defined and met for the ticket list, ticket detail and dashboard under a realistic seeded dataset. 

Test coverage of the SLA engine, authorisation scope and portal visibility allowlist is complete, with golden scenarios passing. 

Any post-freeze contract change is additive within v1, or is explicitly versioned. 

The backend phase exit checklist is signed off before frontend stories begin.
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
