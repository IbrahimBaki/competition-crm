> **Fetched from azure:** [456](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/456)  
> *Fetched 2026-08-26T16:24:12.781Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-09 - Security Baseline: Input Handling, File Uploads, Scanning and Public Endpoint Protection  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, P0-Foundation, Security, SupportCRM

### Description

As a Security-conscious Developer, I want the cross-cutting security rules decided once — validation posture, secret handling, file upload constraints, malware scanning, and protection of anonymous endpoints — so that later stories inherit them instead of each re-deciding what a safe upload is.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/456/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `456` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, P0-Foundation, Security, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-09 - Security Baseline: Input Handling, File Uploads, Scanning and Public Endpoint Protection
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Security-conscious Developer, I want the cross-cutting security rules decided once — validation posture, secret handling, file upload constraints, malware scanning, and protection of anonymous endpoints — so that later stories inherit them instead of each re-deciding what a safe upload is.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
All input is validated server-side by explicit rules; mass assignment is closed by default. 

Secrets and provider credentials live in configuration, never in the repository, and are never returned by any endpoint. 

File uploads enforce a maximum size and an allowlist of types, and store files outside the web root with non-guessable identifiers. 

Uploaded files are scanned; a file pending scan is quarantined and returns a distinct state, and a failed scan is rejected with a distinct error code. 

Anonymous endpoints (web form, help centre, chat widget, survey links) are rate-limited and bot-protected. 

Attachment access is authorised per request; knowing a file identifier is never sufficient to retrieve it.
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
