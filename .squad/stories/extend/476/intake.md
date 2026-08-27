> **Fetched from azure:** [476](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/476)  
> *Fetched 2026-08-27T11:16:33.801Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-29 - Public API, Scoped Tokens, Webhooks, ERP Integration and Bulk Import  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** API, Backend, Integrations, P3-Extended, SupportCRM

### Description

As an Integration Client, I want a documented API with scoped credentials, outbound webhooks, an ERP link and a supported bulk import path, so that external systems can participate without any of them being able to bring the platform down or block an agent's work.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/extend/476/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `extend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `476` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `API, Backend, Integrations, P3-Extended, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-29 - Public API, Scoped Tokens, Webhooks, ERP Integration and Bulk Import
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As an Integration Client, I want a documented API with scoped credentials, outbound webhooks, an ERP link and a supported bulk import path, so that external systems can participate without any of them being able to bring the platform down or block an agent's work.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
API tokens are issued with explicit scopes, are revocable, display their last use, and a request outside its scopes returns a distinct insufficient-scope error naming the missing scope. 

The OpenAPI document is the source of truth, is validated in CI, and no endpoint exists that is absent from it. 

Webhooks deliver ticket lifecycle events with a signed payload, retry with backoff, and expose a delivery log. 

ERP integration is read-oriented for customer and commercial context, with the direction and field set documented. 

Every external dependency degrades gracefully: an ERP or provider outage returns a dependency error and never blocks ticket work. 

Bulk import of existing customers and historical tickets is supported with validation, a dry run, an error report and no partial corruption.
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
