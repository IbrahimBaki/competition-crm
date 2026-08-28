> **Fetched from azure:** [458](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/458)  
> *Fetched 2026-08-26T17:24:14.330Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-11 - Customer Profile, Contact Details, Notes, Attachments and the 360 Timeline  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, Customers, P1-Core, SupportCRM

### Description

As a Support Agent, I want a customer record that holds who they are, every way to reach them, private notes, attached documents and a single chronological view of everything that has happened, so that I never ask a customer to repeat something the company already knows.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/customers/458/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `customers`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `458` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, Customers, P1-Core, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-11 - Customer Profile, Contact Details, Notes, Attachments and the 360 Timeline
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Agent, I want a customer record that holds who they are, every way to reach them, private notes, attached documents and a single chronological view of everything that has happened, so that I never ask a customer to repeat something the company already knows.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
A customer holds name, language preference, status (active/blocked/anonymised) and optional company account with a service tier. 

Contact details are stored as multiple typed identities (email, phone, WhatsApp, portal login), not as single columns on the customer. 

Notes and attachments can be recorded against the customer, attributed and timestamped. 

The 360 timeline returns tickets, messages across all channels, notes and status changes in one paginated chronological feed. 

Customer search matches Arabic name variants (alef/hamza, taa marbuta, diacritics) via a normalised search column, and matches on any contact identity. 

A blocked customer cannot open new tickets through public channels, and the block reason is recorded.
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
