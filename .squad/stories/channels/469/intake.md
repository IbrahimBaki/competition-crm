> **Fetched from azure:** [469](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/469)  
> *Fetched 2026-08-27T00:23:03.128Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-22 - Public Web Form Intake: Configuration, Validation, Spam Control and Duplicate Protection  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Channels, P2-Channels, SupportCRM, WebForm

### Description

As a System Administrator, I want configurable public web forms that create tickets, so that customers without an account have a controlled entry point that is not vulnerable to abuse.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/channels/469/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `channels`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `469` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Channels, P2-Channels, SupportCRM, WebForm`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-22 - Public Web Form Intake: Configuration, Validation, Spam Control and Duplicate Protection
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want configurable public web forms that create tickets, so that customers without an account have a controlled entry point that is not vulnerable to abuse.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Forms are configured (fields, required rules, target department, default category and priority) as data, not code. 

Submissions are validated server-side against the form definition; client validation is never trusted. 

The endpoint is rate-limited and bot-protected, and abusive volume is throttled rather than dropped silently. 

Repeated identical submissions within a short window are deduplicated instead of creating multiple tickets. 

The submitter is resolved to an existing customer through the identity rules, or a new customer is created. 

The submitter receives an acknowledgement containing the ticket reference and a way to track it.
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
