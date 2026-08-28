> **Fetched from azure:** [452](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/452)  
> *Fetched 2026-08-26T12:30:53.718Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-05 - API Foundation: Envelope, Error Catalogue, Pagination, Filtering, Idempotency and Rate Limits  
**Type:** User Story  
**Status:** New  
**Labels:** API, Backend, Blocker, P0-Foundation, SupportCRM

### Description

As a Backend Developer, I want one response envelope, one error shape, one collection query contract, one idempotency mechanism and one rate-limiting policy applied globally, so that the frontend writes its transport layer once and every later endpoint inherits the same behaviour for free.
This story is the single highest-leverage item in the backlog. Without it, every subsequent story invents its own pagination and its own error shape, and the generated client becomes unusable.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/452/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `452` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `API, Backend, Blocker, P0-Foundation, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-05 - API Foundation: Envelope, Error Catalogue, Pagination, Filtering, Idempotency and Rate Limits
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Backend Developer, I want one response envelope, one error shape, one collection query contract, one idempotency mechanism and one rate-limiting policy applied globally, so that the frontend writes its transport layer once and every later endpoint inherits the same behaviour for free.
This story is the single highest-leverage item in the backlog. Without it, every subsequent story invents its own pagination and its own error shape, and the generated client becomes unusable.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Success responses use a single documented envelope; collections always include pagination meta and links; no unpaginated list endpoint exists anywhere. 

Failures use a single shape carrying message, machine-readable code, optional field errors and a request_id echoed in the X-Request-Id header. 

The error code catalogue is implemented as an enumeration, and the client is documented to branch on code and never on message text. 

Every list endpoint supports page, per_page (default 25, max 100, hard 422 above it), whitelisted sort, filter[field] with operators, filter[q], and whitelisted include. 

An Idempotency-Key header on creating endpoints replays the original response within 24 hours instead of creating a duplicate. 

Identifiers exposed by the API are UUIDs; timestamps are ISO-8601 with an explicit offset, never naive and never pre-formatted. 

Rate limiting is applied per authentication context and returns Retry-After.
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
