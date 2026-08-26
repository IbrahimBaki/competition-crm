> **Fetched from azure:** [464](https://dev.azure.com/ibbaqi/Competition-CRM/_workitems/edit/464)  
> *Fetched 2026-08-26T21:07:10.715Z. Edit the sections below as needed; the planner reads this file verbatim.*


## Source — work item (from tracker)

**Title:** BE-17 - SLA Engine: Business-Time Response and Resolution Targets, Pause, Reclassification and Breach Records  
**Type:** User Story  
**Status:** Active  
**Assignee:** Ibrahim Baghdadi  
**Labels:** Backend, Blocker, P1-Core, SLA, SupportCRM

### Description

As a Support Manager, I want first-response and resolution commitments measured in the branch's working time, paused when we are legitimately waiting on the customer, and permanently recorded when breached, so that SLA reporting is defensible rather than approximate.
Depends entirely on the working-time function from BE-02; it must not implement its own time arithmetic.

### Attachments

None.

---
# Story intake

Fill this template for each story you want planned. Keep it copy-paste-friendly: the planner reads **this file and the files in `attachments/`**, nothing else.

- Folder: `.squad/stories/sla/464/intake.md`
- Binaries (screenshots, PDFs, exports): put them in `attachments/` next to this file and list them below.
- Do **not** rely on external links (tracker URLs, wiki, chat) — the planner cannot open them. Paste the content you want considered.

This is **not** an implementation prompt. It is the input to the plan-generation meta-prompt bundled with squad-kit (`generate-plan.md` in the installed package).

---

## Feature

- **Feature name (display):**
- **Feature slug (folder under `plans/`):** `sla`

## Tracker (metadata only)

- **Tracker type:** `azure`
- **Work item id:** `464` *(used in filenames and plan tables; fill manually if empty)*
- **Work item type:** `User Story`
- **Status:** `Active`
- **Assignee:** `Ibrahim Baghdadi`
- **Labels:** `Backend, Blocker, P1-Core, SLA, SupportCRM`

External tracker links are **not** followed by the planner. Keep the id for naming and traceability only.

---

## Title

*(Paste the work item title verbatim. Prefilled when `squad new-story` fetched from a tracker.)*

```
BE-17 - SLA Engine: Business-Time Response and Resolution Targets, Pause, Reclassification and Breach Records
```

---

## Description

*(Paste the full work item description. Prefilled when fetched from a tracker.)*

```
As a Support Manager, I want first-response and resolution commitments measured in the branch's working time, paused when we are legitimately waiting on the customer, and permanently recorded when breached, so that SLA reporting is defensible rather than approximate.
Depends entirely on the working-time function from BE-02; it must not implement its own time arithmetic.
```

---

## Acceptance criteria

*(Checklist, bullets, Gherkin, etc. Prefilled for Azure DevOps when the work item has acceptance criteria.)*

```
SLA policies define first-response and resolution targets per priority, and optionally per category and customer tier; each policy references a branch calendar. 

Elapsed time is computed in working minutes via the shared working-time service, in the ticket's branch time zone. 

Statuses whose lifecycle type pauses the clock do so correctly, and the paused interval is recorded rather than inferred. 

Changing priority or category re-evaluates the target; already-elapsed time carries over and is never reset. A manual SLA reset is a separate, permission-gated, reason-required action. 

A warning threshold fires before a target is missed; a breach writes a permanent record with the target, actual and reason. 

Each ticket exposes its SLA position (target instant, remaining working minutes, state) so the client never recalculates it. 

Golden scenario tests cover pause/resume, reclassification mid-flight, holidays and out-of-hours arrival.
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
