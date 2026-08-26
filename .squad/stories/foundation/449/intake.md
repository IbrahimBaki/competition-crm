> **Fetched from azure:** [449](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/449)  
> *Fetched 2026-08-26T10:45:26.230Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-02 - Branch Business Calendars: Working Hours, Holidays, Time Zones and the Working-Time Function  
**Type:** User Story  
**Status:** New  
**Labels:** Administration, Backend, Blocker, P0-Foundation, SLA, SupportCRM

### Description

As a System Administrator, I want each branch to carry its own working hours, weekend pattern, public holidays and IANA time zone, and I want a single shared function that converts any two instants into elapsed working minutes, so that every SLA number in the system is calculated in business time rather than wall-clock time.
This is deliberately built before the SLA engine, not inside it. The SLA engine, escalation rules, scheduled automations and reports must all call the same function or they will disagree with each other.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/foundation/449/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `foundation`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `449` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `New`
- **Assignee:** ``
- **Labels:** `Administration, Backend, Blocker, P0-Foundation, SLA, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-02 - Branch Business Calendars: Working Hours, Holidays, Time Zones and the Working-Time Function
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a System Administrator, I want each branch to carry its own working hours, weekend pattern, public holidays and IANA time zone, and I want a single shared function that converts any two instants into elapsed working minutes, so that every SLA number in the system is calculated in business time rather than wall-clock time.
This is deliberately built before the SLA engine, not inside it. The SLA engine, escalation rules, scheduled automations and reports must all call the same function or they will disagree with each other.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
A branch has an IANA time zone, a weekly working pattern (per-day open/close, supporting a Sun-Thu week), and a holiday calendar with recurring and one-off dates. 

A 24/7 calendar exists as a supported configuration, not a special case in code. 

A single working-time service exposes: elapsed working minutes between two instants, and the instant reached by adding N working minutes to a start instant. 

All timestamps are stored in UTC; conversion happens only at the boundary, using the branch time zone, never the server time zone. 

Golden test scenarios pass: overnight, across a weekend, across a holiday, across a DST transition, and a start time that falls outside working hours. 

Changing a branch calendar does not retroactively alter already-recorded elapsed time.
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
