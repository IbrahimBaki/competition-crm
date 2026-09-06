# START HERE — Complete Project Walkthrough

This is the shortest high-value journey that crosses staff administration, portal customer service, agent work, collaboration and knowledge. It deliberately separates proven browser flows from async/integration flows that need extra configuration.

## Test Accounts Needed

Use the committed local-only E2E seed accounts in `ROLE_AND_PERMISSION_GUIDE.md`: E2E Admin, Agent A and Portal Customer. Before the journey, use Admin to create/invite Agent B and a Supervisor (or create local accounts and assign the seeded `agent`/`supervisor` roles). All known seed passwords are `E2eSupport!2026`; never substitute production credentials.

## Services That Must Be Running

At minimum: Laravel API, frontend Vite server, database and storage. Start a queue worker (`php artisan queue:work`) for queued messages/notifications/exports; start `php artisan schedule:work` for SLA, automation, reminders, reports and chat sweeps. `composer dev` starts API/Vite/queue but not scheduler. Use a local configured mail sink to exercise reset/invitation email.

## Minimum Seed Data

1. `php artisan migrate` then `php artisan db:seed` for roles, HQ/OPS24 branches, catalogue/SLA baseline.
2. `php artisan db:seed --class=Database\\Seeders\\E2eTestSeeder` for safe personas.
3. Admin-created active department + team; attach Agent A, Agent B and Supervisor to them and HQ.
4. At least one active category/status and an active SLA policy matching HQ. DatabaseSeeder provides categories/SLA where related tables exist.

## Recommended Browser Setup

Use three isolated contexts (or normal window + two private profiles): Staff Admin/Supervisor, Staff Agent, and Portal Customer. Keep the generated ticket ID/reference in a scratchpad. For portal/public visibility, do not reuse staff cookies. Take a screenshot whenever a step says “record.”

## Master Test Sequence

### Step 1 — Bootstrap the safe manual-test users

PERSONA: System operator  
LOGIN AS: none  
ROUTE: terminal  
ACTION: run migrations/standard seed and `E2eTestSeeder` in local/testing only.  
EXPECTED RESULT: E2E Admin, Agent A, Viewer and verified portal customer exist.  
BUSINESS RULE TESTED: test credentials are deliberately local/test scoped.  
NEXT: E2E Admin.

### Step 2 — Confirm administrator entry and configuration landing

PERSONA: Administrator  
LOGIN AS: `e2e.admin@example.test`  
ROUTE: `/login` then `/workspace`  
ACTION: log in and record visible navigation and workspace.  
EXPECTED RESULT: authenticated staff shell and broad admin navigation.  
BUSINESS RULE TESTED: staff session and permission-driven navigation.  
NEXT: configure organisation.

### Step 3 — Create a test support scope

PERSONA: Administrator  
LOGIN AS: `e2e.admin@example.test`  
ROUTE: `/admin/departments`, `/admin/teams`  
ACTION: create active Manual Test Support department and team; record names/IDs.  
EXPECTED RESULT: both are listed as active.  
BUSINESS RULE TESTED: tickets are scoped through organisation structures.  
NEXT: place staff.

### Step 4 — Place Agent A and create Agent B/Supervisor

PERSONA: Administrator  
LOGIN AS: `e2e.admin@example.test`  
ROUTE: `/admin/users`  
ACTION: attach Agent A to HQ/department/team; invite or create local Agent B and Supervisor, give roles and same placement.  
EXPECTED RESULT: all have active usable placement; invitation route is available if invitation is used.  
BUSINESS RULE TESTED: role alone does not grant meaningful team/department visibility.  
NEXT: portal customer.

### Step 5 — Create a customer-originated ticket

PERSONA: Portal Customer  
LOGIN AS: `e2e.portal@example.test`  
ROUTE: `/portal/login` → `/portal/tickets/new`  
ACTION: create a ticket with distinctive subject/body and required category fields; record ticket URL/reference.  
EXPECTED RESULT: new ticket is visible in the portal customer’s ticket list.  
BUSINESS RULE TESTED: portal creates a customer-owned support request.  
NEXT: Agent A.

### Step 6 — Find and claim the request

PERSONA: Support Agent A  
LOGIN AS: `e2e.agent@example.test`  
ROUTE: `/workspace` then `/tickets`  
ACTION: locate the recorded ticket using All/department queue; clear filters if absent; open it and claim/assign where the control is permitted.  
EXPECTED RESULT: agent sees only scoped work and ownership/assignment changes are recorded.  
BUSINESS RULE TESTED: queue and scope visibility, not global portal visibility.  
NEXT: work ticket.

### Step 7 — Separate internal collaboration from customer communication

PERSONA: Support Agent A  
LOGIN AS: `e2e.agent@example.test`  
ROUTE: `/tickets/{ticketId}`  
ACTION: set status to Open; add an internal note; then send a public reply. Record conversation and history.  
EXPECTED RESULT: both entries appear to staff, but only public reply can appear to portal.  
BUSINESS RULE TESTED: note visibility and ticket lifecycle.  
NEXT: assign a task.

### Step 8 — Create cross-agent downstream work

PERSONA: Support Agent A  
LOGIN AS: `e2e.agent@example.test`  
ROUTE: `/tickets/{ticketId}` Ticket Tasks  
ACTION: create a task owned by Agent B, due soon, linked to the ticket.  
EXPECTED RESULT: open task is shown on ticket; Agent B becomes next actor.  
BUSINESS RULE TESTED: a ticket can delegate work without transferring customer ownership.  
NEXT: Agent B.

### Step 9 — Complete delegated work

PERSONA: Support Agent B  
LOGIN AS: locally created Agent B  
ROUTE: `/workspace`  
ACTION: open own task, move to In progress, then Done; record state.  
EXPECTED RESULT: task state/timestamps update; it is terminal when Done.  
BUSINESS RULE TESTED: task ownership and state machine.  
NEXT: Agent A or Supervisor.

### Step 10 — Verify collaboration and resolve

PERSONA: Support Agent A  
LOGIN AS: `e2e.agent@example.test`  
ROUTE: `/tickets/{ticketId}`  
ACTION: verify completed task, optionally watch/mention an eligible supervisor internally, send final public reply and move ticket to Resolved.  
EXPECTED RESULT: ticket history/SLA panel reflect changes; resolved ticket may leave an open-only filter.  
BUSINESS RULE TESTED: staff action creates both collaboration history and customer-facing outcome.  
NEXT: Portal Customer for step 11.

### Step 11 — Confirm the customer loop

PERSONA: Portal Customer  
LOGIN AS: `e2e.portal@example.test`  
ROUTE: `/portal/tickets/{ticketId}`  
ACTION: confirm the public reply, confirm internal note is absent, optionally submit allowed feedback or a follow-up message.  
EXPECTED RESULT: isolation holds; customer sees only own public conversation.  
BUSINESS RULE TESTED: portal isolation and public/internal boundary.  
NEXT: Supervisor.

### Step 12 — Supervise, report and publish knowledge

PERSONA: Supervisor then Administrator  
LOGIN AS: locally created Supervisor, then `e2e.admin@example.test`  
ROUTE: `/tickets/{ticketId}`, `/reports`, `/knowledge/new`, `/knowledge/{id}`, `/portal/help`  
ACTION: supervisor inspects team/department work; produce a draft solution article; Admin publishes it; clean portal browser searches it. Run export/scheduler only with queue/scheduler running.  
EXPECTED RESULT: supervisor scope works, published eligible knowledge becomes public, report/export job begins.  
BUSINESS RULE TESTED: management oversight and knowledge feedback loop.  
NEXT: optional channel/SLA/automation integrations below.

## Optional extensions

Test admin 2FA policy + a new invitation, customer merge/duplicates, saved views/bulk ticket actions, public web form/live chat, provider webhooks/delivery receipts, AI suggestions, automation/SLA time passage, report schedules, API tokens/webhooks/imports. Their prerequisites and known UI/API gaps are in `END_TO_END_BUSINESS_FLOWS.md` and `PROJECT_GAPS_AND_AMBIGUITIES.md`.
