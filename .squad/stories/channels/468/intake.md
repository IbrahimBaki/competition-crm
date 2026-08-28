> **Fetched from azure:** [468](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/468)  
> *Fetched 2026-08-27T00:01:23.656Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-21 - Email Channel: Threading, Correlation, Loop Prevention, Bounces and Replay  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Channels, Email, P2-Channels, SupportCRM

### Description

As a Support Agent, I want inbound email to land on the right ticket and outbound email to reach the customer reliably, so that the primary support channel does not silently create duplicate tickets or lose replies.
This is the first channel implemented because it is the highest volume and the one whose failure modes are hardest to retrofit.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/channels/468/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `channels`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `468` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Channels, Email, P2-Channels, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-21 - Email Channel: Threading, Correlation, Loop Prevention, Bounces and Replay
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want inbound email to land on the right ticket and outbound email to reach the customer reliably, so that the primary support channel does not silently create duplicate tickets or lose replies.
This is the first channel implemented because it is the highest volume and the one whose failure modes are hardest to retrofit.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Inbound mail is correlated to an existing ticket by message headers first and a reference token second; correlation failure creates a new ticket rather than discarding the mail. 

Auto-replies, out-of-office and bounce messages are detected and do not create tickets or trigger customer replies. 

Mail loops are prevented by a documented mechanism, and a loop condition raises an operational alert. 

Quoted history and signatures are stripped from the stored message body while the raw source is retained. 

Bounces and permanent failures update the message delivery state and surface to the agent. 

Inbound processing failures are queued and replayable by an operator; a provider outage loses no mail.
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
