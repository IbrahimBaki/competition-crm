> **Fetched from azure:** [479](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/479)  
> *Fetched 2026-08-27T15:47:09.130Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** FE-02 - Presentation Layer: Arabic/English Switching, RTL Layout, Locale Formatting and Branding  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Blocker, Frontend, Localization, P4-Frontend, SupportCRM

### Description

As a Customer's Administrator, I want the whole interface to switch between Arabic and English with correct right-to-left behaviour and correct local formatting, and to carry our own logo and colours, so that Arabic is a first-class experience rather than a mirrored afterthought.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/frontend/479/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `frontend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `479` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Blocker, Frontend, Localization, P4-Frontend, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
FE-02 - Presentation Layer: Arabic/English Switching, RTL Layout, Locale Formatting and Branding
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Customer's Administrator, I want the whole interface to switch between Arabic and English with correct right-to-left behaviour and correct local formatting, and to carry our own logo and colours, so that Arabic is a first-class experience rather than a mirrored afterthought.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Language switches at runtime without a reload and persists per user. 

Layout uses logical CSS properties throughout; direction flips correctly including icons, tables, charts, form alignment and scroll behaviour. 

Dates, times, numbers and durations are formatted per locale, and every instant is displayed in the correct zone as supplied by the API. 

No user-facing string is hardcoded in a component; all come from translation files. 

Bilingual fields from the API render in the active locale with the documented fallback. 

Organisation logo and primary colours apply across the workspace, the portal and email templates. 

<li>Visual design (colors, typography, spacing, and component styling) follows the reference design at `docs/ui/customer-portal-reference.html` as the baseline theme for the whole application, not only the portal; screens not covered by the reference must reuse its color tokens, type scale, and component patterns rather than introduce a new style language.</li>
 

An RTL defect blocks the story: every screen is reviewed in Arabic before it is accepted.
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
