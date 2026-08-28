> **Fetched from azure:** [472](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/472)  
> *Fetched 2026-08-27T02:35:37.289Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-25 - Knowledge Base: Authoring Workflow, Versioning, Visibility Levels and Bilingual Search  
**Type:** User Story  
**Status:** New  
**Labels:** Backend, KnowledgeBase, P3-Extended, SupportCRM

### Description

As a Knowledge Author, I want to write, get approved, publish and version help content with controlled visibility, so that the same content can safely serve customers, portal users and staff without exposing internal material.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/extend/472/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `extend`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `472` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Backend, KnowledgeBase, P3-Extended, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-25 - Knowledge Base: Authoring Workflow, Versioning, Visibility Levels and Bilingual Search
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Knowledge Author, I want to write, get approved, publish and version help content with controlled visibility, so that the same content can safely serve customers, portal users and staff without exposing internal material.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
Articles move through draft, review and published states, with publication gated by its own permission key and archiving supported. 

Every publication creates a version; previous versions are viewable and restorable. 

Visibility is explicit per article (public, registered customers, staff only) and enforced at the query level, not by filtering after fetch. 

An internal article cannot be retrieved through any customer-facing endpoint even when its identifier is known. 

Search works in Arabic and English using the normalised search columns, and never returns content outside the caller's audience. 

Articles are organised in a browsable category structure, can be inserted into an agent reply, and collect helpful/not-helpful feedback.
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
