# End-to-end business flows

## Flow 1 — Portal customer request to staff response

**Preconditions:** E2E seed run; Admin has created an active department and assigned Agent A to it (and preferably a team); a usable category/status catalogue exists. Queue worker should run if testing provider delivery; portal flow itself is synchronous.

**LOGIN 1**  
Role: Portal account  
User: `e2e.portal@example.test`  
Route: `/portal/login`

**ACTION 1**  
Log in, open `/portal/tickets/new`, enter a clear subject/body and choose available category/priority fields, then submit.  
Expected immediate result: ticket confirmation/list entry and a customer-owned `new` ticket.  
Database/business effect: ticket/customer relationship, initial status and events; routing/SLA logic executes where configured.

**LOG OUT**

**LOGIN 2**  
Role: Agent  
User: `e2e.agent@example.test`  
Route: `/workspace`, then `/tickets`

**EXPECTED RESULT**  
Agent sees the ticket only if its department/team/own scope permits it; use All/department queue and clear filters. This proves that portal isolation does not equal unassigned-ticket invisibility.

**ACTION 2**  
Open `/tickets/{ticketId}`, claim/assign if control is available, change to `open`, add an **internal note** explaining the diagnosis, then send a **public reply**.  
Expected immediate result: timeline/history and conversation update; internal note is staff-only; public message is available to portal. A ticket may move out of a filtered queue after its status changes.

**LOG OUT → LOGIN 3**  
Role/User: Portal customer / `e2e.portal@example.test`  
Route: `/portal/tickets/{ticketId}`

**EXPECTED RESULT / COMPLETE**  
Public reply appears; the internal note does not. Customer may reply or submit feedback if the ticket lifecycle permits. Evidence: `PortalTicketVisibilityTest.php`, `TicketInternalNoteVisibilityTest.php`, `PostTicketMessage.php`.

## Flow 2 — Supervisor collaboration through a task

**Preconditions:** create a locally invited Supervisor and Agent B, both placed in the same department/team as Agent A; an open visible ticket exists.

**LOGIN 1:** Agent A at `/tickets/{ticketId}`.  
**ACTION 1:** In Ticket Tasks, create a task with Agent B as owner, meaningful title/due time, and ticket linkage.  
**Result:** Agent task starts `open`; Agent B is the intended downstream actor. Creator sees it based on their permitted scope.

**LOG OUT → LOGIN 2:** Agent B at `/workspace`.  
**EXPECTED RESULT:** own task appears, including overdue status when due date is passed.  
**ACTION 2:** move it `open → in_progress → done`.  
**Result:** completion timestamp and task event are written; originating ticket task panel reflects Done.

**LOG OUT → LOGIN 3:** Supervisor at `/tickets/{ticketId}` or `/workspace`.  
**EXPECTED RESULT / COMPLETE:** supervisor can inspect others’ task state and, if necessary, reassign task. Terminal task cannot be reopened. Evidence: `ChangeAgentTaskState.php`, `AgentTaskApiTest.php`.

## Flow 3 — Admin configuration causing operational behaviour

**LOGIN 1**  
Role: Administrator; user `e2e.admin@example.test`; route `/admin/departments`, `/admin/teams`, `/admin/users`.

**ACTION 1** Create active “Manual Test Support” department and team; place Agent A/Agent B in it; ensure HQ primary branch. Create a category/custom field on `/admin/ticket-catalogue`; inspect/create SLA policy target on `/admin/sla-policies` (write permission applies).  
**Effect:** agents gain a scope container; category makes its fields required and branch calendar/SLA targets become eligible for new tickets.

**LOG OUT → LOGIN 2** Agent A `/tickets/new`.  
**EXPECTED RESULT:** department/category choices and relevant custom fields are available; resulting ticket is visible to appropriate department/team scope and SLA panel can show policy/clock if matched.  
**NEXT:** portal customer or Agent B validates the new ticket.

## Flow 4 — Knowledge authoring to public self-service

**Preconditions:** author has create/update; publisher has publish; category exists. An agent can author but cannot publish under default seed permissions.

**LOGIN 1:** Agent A `/knowledge/new`.  
**ACTION 1:** create an internal or public-visibility draft, then submit it for review from `/knowledge/{id}` if lifecycle UI permits.  
**Result:** `draft → in_review`; it is not public yet.

**LOG OUT → LOGIN 2:** Supervisor or Admin `/knowledge/{id}`.  
**ACTION 2:** publish after reviewing.  
**Result:** `in_review → published`; public rendering/search eligibility follows visibility.

**LOG OUT → LOGIN 3:** clean browser visitor `/portal/help`.  
**EXPECTED RESULT / COMPLETE:** published eligible article is searchable/readable. Archive removes it from the public lifecycle. Evidence: `ArticleTransitionMap.php`, `KnowledgeVisibilityTest.php`, `KnowledgeVersioningTest.php`.

## Flow 5 — Authentication/invitation/2FA

**LOGIN 1:** Admin `/admin/users`.  
**ACTION 1:** invite a new local mailbox recipient, give intended role and placement.  
**Result:** invitation record/email (mail transport must be configured) with token URL.

**LOG OUT → LOGIN 2:** recipient `/invitations/{token}`.  
**ACTION 2:** accept and establish password, then `/login`.  
**Result:** active staff session, subject to org 2FA policy.

**ACTION 3:** `/account` → set up authenticator → store recovery codes → confirm code; logout/login to validate challenge.  
**COMPLETE:** use password-reset route only against a safely controlled local mailbox. Tests establish expiry/replay/error rules: `InvitationFlowTest.php`, `TwoFactorPolicyTest.php`, `PasswordResetTest.php`.

## Flow 6 — SLA and automation (time-dependent)

Create/retain a ticket matched to active branch/policy/priority target. At creation, inspect SLA panel. Change to pending/resolved/closed/spam to verify clock stops; open resumes eligible work. To test a warning/breach or scheduled stale nudge, run scheduler and use controlled local timestamps/short local policy rather than waiting production durations. The seeded rules (when explicitly seeded) escalate SLA warning, notify assigned agent for 24-hour agent inactivity, and auto-close after 7-day customer inactivity. See `SlaPolicySeeder.php`, `AutomationRulesSeeder.php`, `routes/console.php`.

## Flow 7 — Public form/live chat (configuration and external-state dependent)

Admin configures active form at `/admin/channels`, then a clean visitor visits `/forms/{formKey}` and submits; staff finds the resulting channel work/ticket according to backend configuration and tracks via token URL. For chat, visitor `/chat` starts session; staff `/admin/channels` Chat tab accepts, messages, transfers or ends. These flows need configuration and potentially a scheduler to abandon stale chat; they are not included in the minimal master journey because E2E seed creates no web form and the UI exposes UUID-heavy operator inputs. Evidence: `WebFormSubmissionTest.php`, `ChatSessionLifecycleTest.php`.

## Flow 8 — Reports/export and notifications

Create tickets/messages first. Manager/Admin opens `/reports`, selects a report and filter range, then requests export. A queued `GenerateReportExportJob` changes the export state. Schedule a report at `/report-schedules` to exercise `report:schedule-sweep`. Notifications caused by mention/watch/assignment/escalation appear on workspace for eligible recipient and can be marked read. Browser verification of completed export retrieval is blocked by missing documented SPA endpoint; see gaps.
