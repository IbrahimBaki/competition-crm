# Known flow gaps and ambiguities

This is an evidence-based list, not a repair plan. Severity is based on manual-browser testing impact.

| Severity | Expected behaviour / documentation claim | Actual implementation evidence | Impact on manual testing |
|---|---|---|---|
| P1 | All default roles should be manually exercisable from one safe seed. | `E2eTestSeeder.php` creates only administrator, agent, viewer and portal account, all HQ-only; no manager/supervisor/second agent/team/department. | Create local personas/placement before cross-role tests. |
| P1 | A portal-created ticket should reliably appear to an E2E agent. | Agent has team/own visibility; seed has no department/team placement and creates no ticket-routing test data. `PermissionsAndRolesSeeder.php`, `E2eTestSeeder.php`. | Admin must configure assignment/scope first; otherwise “missing ticket” is expected/ambiguous. |
| P1 | Every supported ticket lifecycle operation should be browser-accessible. | API provides reopen, spam restore/mark, transfer, links, merge/split/tags, but current `TicketDetailPage.tsx` exposes status, assignment, conversation, watchers/tasks/history/AI—not a complete UI for all endpoints. | Test the workbench-supported path in browser; advanced operations require API testing until UI is added. |
| P1 | Ticket attachment lifecycle is a normal complete browser flow. | backend supports generic attachments and ticket message attachment validation; no clear attachment picker is present in `TicketComposer.tsx` and no full attachment UI is routed. | Do not mark portal/staff attachment flow passing from UI alone. |
| P1 | Reports export can be completed/downloaded in SPA. | `POST /reports/{report}/export` queues `GenerateReportExportJob`; model has ready state, but frontend `ExportMenu.tsx` notes no status/download route. | Can verify request/job state only through backend/DB/admin evidence; end-user download is incomplete/undocumented. |
| P2 | README says broad channel integrations are implemented. | Web form/chat browser pages exist, but no seed form/provider setup; email/SMS/WhatsApp depend on webhook credentials/providers and jobs. | Configuration/real external traffic required; do not fabricate result. |
| P2 | All backend customer operations have matching screens. | Contacts CRUD, block/unblock, attachments and duplicate actions exist as APIs; Customer Detail implements a subset/permission-dependent panels. | Check screen availability separately; use API tests as behaviour evidence. |
| P2 | Knowledge publishing flow can be performed by ordinary E2E agent. | Agent seed has create/update but not `knowledge.articles.publish`; Supervisor/Admin has publish. | Use two users for author/reviewer/publisher journey. |
| P2 | Automation rules seeded by standard bootstrap. | `DatabaseSeeder.php` does **not** call `AutomationRulesSeeder.php`, though seed file defines three rules. | Explicitly run it locally to expect those automation examples. |
| P2 | SLA baseline always exists. | Seeder skips SLA tables/branch absence and uses first branch; it runs before/alongside branch creation order concerns (`DatabaseSeeder.php` calls SLA before catalogue, but branches seed happens earlier). | Verify policy/targets in UI/DB before judging SLA panel. |
| P2 | Scheduler behaviour occurs with normal developer command. | `composer dev` starts queue listener but `routes/console.php` schedules separate commands. | Start `schedule:work`; otherwise reminders, sweeps, report schedules and SLA changes will not arrive. |
| P3 | 2FA is always a login step. | It is policy/account dependent; user can enrol via `/account`, policy is at `/admin/settings`. | Test conditionally, not as universal default. |
| P3 | README/module list equals frontend reachability. | Router is narrower/mixed V2; several admin operations are generic endpoint tables and some advanced backend operations have no route/control. | Use `PAGE_BY_PAGE_GUIDE.md` as browser inventory and README as secondary context. |

## Documentation disagreements reconciled

The README’s claim that the project supports the listed domains is backed by routes/domains/tests. Its “implemented” wording should not be read as “every endpoint has a polished, directly connected browser flow.” The authoritative current behaviour is the backend action/policy/tests plus the current router/components. This is why the playbook distinguishes configuration/API-supported scenarios from the recommended browser journey.

## Things that are deliberately not asserted

- No real provider credentials, external destination URL, or production email/password is documented.
- Session-expiry redirect timing is not assigned a numeric duration here; it is configuration/session dependent. Verify by expiring a local session.
- A report export completion cannot be claimed visible/downloadable from supplied SPA code without an additional endpoint/UI.
- A direct URL success does not prove authorization: policies can still return 403 based on record scope.
