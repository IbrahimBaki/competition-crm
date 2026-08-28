> **Fetched from azure:** [478](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/478)  
> *Fetched 2026-08-27T15:06:39.054Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-01 - SPA Shell: Generated Client, Authentication, Error Handling and Permission-Driven Navigation  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Blocker, Frontend, P4-Frontend, Shell, SupportCRM

### Description

As a Frontend Developer, I want the application shell — a client generated from the OpenAPI document, the authentication flow, one place that understands the error envelope, and navigation built from permission keys — so that every screen after this one is composition rather than plumbing.
Recommended to be pulled forward and built as a thin slice against BE-05 plus BE-13 before the rest of the backend completes, purely to prove the contract is usable. See the notes on the phase gate.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/478/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `478` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Blocker, Frontend, P4-Frontend, Shell, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-01 - SPA Shell: Generated Client, Authentication, Error Handling and Permission-Driven Navigation
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Frontend Developer, I want the application shell — a client generated from the OpenAPI document, the authentication flow, one place that understands the error envelope, and navigation built from permission keys — so that every screen after this one is composition rather than plumbing.
Recommended to be pulled forward and built as a thin slice against BE-05 plus BE-13 before the rest of the backend completes, purely to prove the contract is usable. See the notes on the phase gate.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
The API client is generated from the OpenAPI document, not hand-written, and regenerating it is a documented command. 

Login, CSRF handling, session expiry and silent re-authentication work end to end. 

One transport layer handles the success envelope, the error envelope, pagination and the request ID; no screen parses errors itself. 

Navigation and action visibility are driven by the permission key list from the current-user endpoint; no role name appears anywhere in client code. 

Loading, empty, error and forbidden states are shell-level patterns, defined once. 

A protected route accessed without permission shows the correct state rather than a broken screen.
<li>The frontend is implemented as a React SPA living in a `frontend/` directory inside this same Laravel repository (monorepo) — not a separate repository, not Filament, not any other framework.</li>
<li>The OpenAPI client is generated using `orval`, producing TanStack Query hooks from the `openapi.yaml` contract; the generation command is documented in the repository README.</li>
 

<li>Authentication uses Laravel Sanctum with a domain-split setup: the frontend is served from an `app.<subdomain>` origin and the API from the bare `<subdomain>` origin, so that Sanctum's cookie-based SPA authentication works correctly across the two origins.</li>
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
