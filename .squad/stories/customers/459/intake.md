> **Fetched from azure:** [459](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/459)  
> *Fetched 2026-08-26T17:58:39.561Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-12 - Canonical Customer Identity Across Channels and Safe Duplicate Merge  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, Channels, Customers, P1-Core, SupportCRM

### Description

As a Support Manager, I want the same person arriving by email, WhatsApp, SMS, web form, chat or portal to resolve to one customer record, and I want a safe way to merge duplicates that already exist, so that interaction history and service tier are actually complete.
This is placed immediately after the customer record and before tickets on purpose: if identity resolution arrives late, the ticket table is already full of orphaned duplicates.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/customers/459/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `customers`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `459` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, Channels, Customers, P1-Core, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-12 - Canonical Customer Identity Across Channels and Safe Duplicate Merge
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager, I want the same person arriving by email, WhatsApp, SMS, web form, chat or portal to resolve to one customer record, and I want a safe way to merge duplicates that already exist, so that interaction history and service tier are actually complete.
This is placed immediately after the customer record and before tickets on purpose: if identity resolution arrives late, the ticket table is already full of orphaned duplicates.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
An inbound contact on any channel is matched to an existing customer by its channel identity before a new customer is created. 

Matching rules are explicit and documented (exact identity match; normalised phone comparison including country code), and ambiguous cases create a review item rather than guessing. 

A customer may hold several identities of the same type, and an identity belongs to exactly one customer. 

Duplicate candidates are surfaced for review with the evidence that flagged them. 

Merging moves tickets, messages, notes, attachments and identities to the surviving record, is audited, and leaves no orphan rows. 

Merge is permission-gated and the merged-away identifier still resolves to the survivor so old links do not break.
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
