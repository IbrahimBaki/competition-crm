> **Fetched from azure:** [462](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/462)  
> *Fetched 2026-08-26T19:58:42.789Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-15 - Unified Conversation Model: Messages, Internal Notes, Attachments and Delivery State  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Channels, P1-Core, SupportCRM, Tickets

### Description

As a Backend Developer, I want one conversation model shared by every channel — carrying direction, author type, channel, body, attachments and delivery state — so that adding a channel later is a provider adapter rather than a new data model.
Built before any individual channel deliberately: email, WhatsApp, SMS and chat then all write into the same table.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/tickets/462/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `tickets`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `462` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Channels, P1-Core, SupportCRM, Tickets`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-15 - Unified Conversation Model: Messages, Internal Notes, Attachments and Delivery State
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Backend Developer, I want one conversation model shared by every channel — carrying direction, author type, channel, body, attachments and delivery state — so that adding a channel later is a provider adapter rather than a new data model.
Built before any individual channel deliberately: email, WhatsApp, SMS and chat then all write into the same table.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
A message records direction (inbound/outbound), author type (customer/agent/ai/system), source channel, body, and attachments. 

Internal notes live in the same thread but are structurally marked and are never serialised by any customer-facing endpoint. 

Outbound messages carry a delivery state lifecycle (queued, sent, delivered, read where supported, failed) with a failure reason. 

A failed outbound message is visible to the agent and retryable, and never silently disappears. 

Sending is idempotent: a client retry with the same Idempotency-Key never produces two customer messages. 

Attachments on messages obey the security baseline for size, type, scanning and authorised access.
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
