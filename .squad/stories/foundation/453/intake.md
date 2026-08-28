> **Fetched from azure:** [453](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/453)  
> *Fetched 2026-08-26T14:58:57.013Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-06 - Localization Architecture: Bilingual Content, Locale Negotiation and Zero Hardcoded Strings  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, Blocker, Localization, P0-Foundation, Platform, SupportCRM

### Description

As a Product Owner, I want Arabic and English handled as an architectural property of the data and the API rather than as a translation pass at the end, so that adding Arabic never requires rewriting screens or migrations.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/453/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `453` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, Blocker, Localization, P0-Foundation, Platform, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-06 - Localization Architecture: Bilingual Content, Locale Negotiation and Zero Hardcoded Strings
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Product Owner, I want Arabic and English handled as an architectural property of the data and the API rather than as a translation pass at the end, so that adding Arabic never requires rewriting screens or migrations.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Any administrator-authored name or label is stored bilingually and returned as an object containing both locales, so the client can switch language without a second request. 

Request locale resolves from Accept-Language, then user profile, then organisation default. 

The server translates its own messages but never returns UI chrome text; the client owns chrome. 

No user-facing string is hardcoded in backend code; all pass through translation files. 

A defined fallback applies when one locale of a bilingual field is empty, and it is documented rather than accidental. 

Arabic text is stored and retrieved without corruption end to end (utf8mb4), including in search, exports and email.
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
