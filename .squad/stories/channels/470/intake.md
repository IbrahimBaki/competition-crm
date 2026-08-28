> **Fetched from azure:** [470](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/470)  
> *Fetched 2026-08-27T00:23:22.931Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-23 - Provider Messaging Channels: WhatsApp and SMS Windows, Templates, Opt-In/Opt-Out and Delivery Status  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Channels, P2-Channels, SMS, SupportCRM, WhatsApp

### Description

As a Support Agent, I want WhatsApp and SMS to work within the rules the providers impose, so that a send either succeeds or fails for an understandable reason rather than disappearing.
Grouped into one story because both are outbound-restricted provider channels sharing the same pattern: a permission to message, an approved template path, and an asynchronous delivery receipt.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/channels/470/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `channels`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `470` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Channels, P2-Channels, SMS, SupportCRM, WhatsApp`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-23 - Provider Messaging Channels: WhatsApp and SMS Windows, Templates, Opt-In/Opt-Out and Delivery Status
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want WhatsApp and SMS to work within the rules the providers impose, so that a send either succeeds or fails for an understandable reason rather than disappearing.
Grouped into one story because both are outbound-restricted provider channels sharing the same pattern: a permission to message, an approved template path, and an asynchronous delivery receipt.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
The WhatsApp free-form window is enforced server-side; a send outside it is rejected with a distinct error code and the approved-template path is offered instead. 

Approved templates are stored with their variables and rendered bilingually. 

Consent is recorded: opt-in for WhatsApp, opt-out honoured for SMS, both enforced before any send. 

Provider delivery receipts update the message delivery state asynchronously. 

Inbound messages with no subject derive one from the opening characters of the message body. 

A provider outage degrades gracefully: the message queues and retries, and the agent sees the real state.
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
