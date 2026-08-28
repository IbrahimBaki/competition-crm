> **Fetched from azure:** [473](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/473)  
> *Fetched 2026-08-27T09:12:35.115Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-26 - AI Assistance and Customer Chatbot with Mandatory Human Approval and Safe Provider Failure  
**Type:** User Story  
**Status:** New  
**Labels:** AI, Backend, P3-Extended, SupportCRM

### Description

As a Support Manager, I want AI to draft, summarise, classify and suggest for agents, and to answer customers only through one explicitly labelled chatbot, so that AI accelerates the team without ever speaking for the company unreviewed.
Two paths, one safety model: the agent-facing path always ends at a human; the customer-facing path is labelled, restricted to approved knowledge, and always has an escape hatch to a person.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/extend/473/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `extend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `473` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `AI, Backend, P3-Extended, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-26 - AI Assistance and Customer Chatbot with Mandatory Human Approval and Safe Provider Failure
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager, I want AI to draft, summarise, classify and suggest for agents, and to answer customers only through one explicitly labelled chatbot, so that AI accelerates the team without ever speaking for the company unreviewed.
Two paths, one safety model: the agent-facing path always ends at a human; the customer-facing path is labelled, restricted to approved knowledge, and always has an escape hatch to a person.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
No AI-generated content reaches a customer through the agent path without an explicit human send action; this is enforced server-side, not by UI convention. 

Agent-facing features (summary, suggested reply, auto-classification, suggested articles) are individually toggleable. 

Auto-classification writes a confidence score; below a configured threshold the ticket is routed to a human instead of auto-labelled, and the AI-assigned label is distinguishable from a human one. 

The chatbot is visibly labelled as automated, answers only from approved published knowledge, and hands off to a human on request or on repeated failure. 

Provider unavailability returns a distinct dependency error and degrades the feature; no ticket workflow is ever blocked by an AI outage. 

Usage and cost are tracked against a configurable budget, and personal data sent to the provider follows a documented policy.
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
