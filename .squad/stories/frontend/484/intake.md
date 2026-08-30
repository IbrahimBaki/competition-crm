> **Fetched from azure:** [484](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/484)  
> *Fetched 2026-08-27T18:58:10.814Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-07 - Reports, Dashboards and the Customer Portal UI  
**Type:** User Story  
**Status:** New  
**Labels:** CustomerPortal, Frontend, P4-Frontend, Reporting, SupportCRM

### Description

As a Support Manager and as a Customer, I want the reporting surface and the self-service portal, so that performance is visible internally and customers can serve themselves externally.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/484/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `484` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `CustomerPortal, Frontend, P4-Frontend, Reporting, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-07 - Reports, Dashboards and the Customer Portal UI
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager and as a Customer, I want the reporting surface and the self-service portal, so that performance is visible internally and customers can serve themselves externally.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Reports share one filter bar, and every figure agrees with the ticket detail it came from. 

The management dashboard summarises operational health on one screen and respects the caller's scope. 

Export produces CSV, Excel and PDF, with correct Arabic rendering and direction in the PDF output. 

The portal is a separate application surface with its own authentication, branded per the organisation. 

<li>The Customer Portal's visual implementation matches the reference design (`docs/ui/customer-portal-reference.html`) exactly for layout structure and component styling; any state not shown in the reference (loading, error, empty, guest-tracking) must visually extend it rather than diverge from it.</li>
 

A customer can submit, track, reply to, and rate a request, and browse published help content. 

A guest can track a single request through their link without an account. 

Nothing internal is reachable in the portal, including by manipulating an identifier in the URL.
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
