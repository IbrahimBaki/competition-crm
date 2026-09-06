# Roles, personas and access guide

## Access model

Staff access is an aggregate of role permission keys, not a runtime comparison to a role name. The client hides routes/actions through `ProtectedRoute`, `RequirePermission` and `ActionGuard`, but Laravel Policies and scope resolution are final enforcement. A user can have multiple roles and custom roles. Ticket/report scopes are `own`, `team`, `department`, `branch`, and `any`; organisational placement supplies the context. Evidence: `PermissionKey.php`, `ResolveEffectiveScope.php`, `TicketPolicy.php`, `PermissionsAndRolesSeeder.php`.

| Role | Purpose | Login area / landing | Main modules | Receives work from | Sends work to / key limits |
|---|---|---|---|---|---|
| `administrator` | Complete system control. | `/login` → `/` workspace | all modules, including audit/data protection/integrations/AI | all actors | all; must preserve the last active administrator invariant |
| `manager` | Runs operations across all tickets and a branch reporting scope. | staff → workspace | org/admin, any-ticket work, customers, SLA/automation, channel administration, knowledge, report exports/schedules | customers, agents, automation | can transfer agent/department and administer; does **not** receive administrator-only audit/data-protection/integration/AI setting permissions in the seeded role |
| `supervisor` | Leads department/team operational work. | staff → workspace | department/team ticket work, tasks, customer operations, SLA visibility/reset, chat, knowledge, department reporting | team/department queues and agents | no transfer-to-agent/department in seeded set; no SLA/rule management, user/role admin or report export/schedule |
| `agent` | Resolves own/team support work. | staff → workspace | own/team tickets, claim/assign, messages/internal notes, customer context/notes, own tasks, queues, knowledge edit, AI assist, chat | portal/public/channel tickets, supervisor assignment | no org/admin/customer edit or contact management; own report scope; cannot see other users’ tasks |
| `viewer` | Read-only limited staff observer. | staff → workspace | only own ticket view/message read/attachment download, own notifications | ticket ownership only | cannot create/update/reply/claim, customers, knowledge, reports, queues, tasks |
| Portal account (not a staff role) | Customer self-service identity associated with one customer. | `/portal/login`; ticket list after login | own portal tickets/messages/downloads/account, public knowledge, feedback | staff public replies | cannot see staff notes or another customer’s ticket; separate guard/token |
| Public visitor (not authenticated) | Submit a configured form, start chat, browse public knowledge, use guest tracking token. | `/forms/:formKey`, `/chat`, `/portal/help`, `/portal/track/:token` | public surfaces only | configured channel intake | cannot access staff/portal authenticated pages |

### Seeded/default role permissions at a glance

| Capability | Administrator | Manager | Supervisor | Agent | Viewer |
|---|---:|---:|---:|---:|---:|
| Ticket visibility | any | any | team + department | team + own | own |
| Create/reply/internal note | yes | yes | yes | yes | no |
| Claim ticket / queue | yes | not explicitly claim/queue | not explicitly queue | yes | no |
| Assign | yes | yes | yes | yes | no |
| Transfer agent/department | yes | yes | no | no | no |
| Customer create/edit/merge | yes | yes | create/edit/merge except customer merge is absent | read/notes/timeline only | no |
| Tasks | all | create/view others/reassign | create/view others/reassign | create/view own | no |
| Publish/archive knowledge | yes | yes | yes | create/update only | no |
| Reports | any/export/schedule | branch/export/schedule | department | own | no |
| Admin configuration | all | most users/org/SLA/rules/channels | no manage | no | no |

“Not explicitly” is significant: the default role has no corresponding permission in `PermissionsAndRolesSeeder.php`; a custom role could add it.

## Safe local test personas

Run `php artisan db:seed --class=Database\\Seeders\\E2eTestSeeder` in `local` or `testing`. It deliberately commits the following credentials for E2E use. It only creates Admin/Agent/Viewer and an already verified portal account.

| Test persona | Role | Email | Password availability | Department/team | Purpose |
|---|---|---|---|---|---|
| E2E Admin | `administrator` | `e2e.admin@example.test` | `E2eSupport!2026` | HQ branch only | configure/create required people/data |
| E2E Agent A | `agent` | `e2e.agent@example.test` | `E2eSupport!2026` | HQ branch only; **no seeded department/team** | agent work after placement |
| E2E Viewer | `viewer` | `e2e.viewer@example.test` | `E2eSupport!2026` | HQ branch only | prove read-only restrictions; needs owned ticket |
| E2E Portal Customer | portal account | `e2e.portal@example.test` | `E2eSupport!2026` | customer `E2E Portal Customer` | portal create/reply/visibility |
| Demo roles | all five role names | `administrator+demo@example.com`, etc. | Password not safely discoverable from repository — reset/create local test user. | HQ only | demo identities only if `DemoSeeder` is invoked |

### Missing test personas

`E2eTestSeeder` does not provide a manager, supervisor, second agent, a department/team assignment, a configured web form, or a staff invitation recipient. Create them locally with the E2E Admin: invite a second agent (accept invitation), create a department and team, attach Agent A and B to the department/team, and create a manager/supervisor custom local account or run the safe DemoSeeder then reset its passwords. Do not use environment-provided admin credentials in this guide.

## Navigation and access dependencies

The sidebar uses permission filtering in `frontend/src/shell/navigation.ts`. A direct URL is also guarded by `frontend/src/router.tsx`, but page/action availability can be narrower than access to its parent route. Example: `/admin/sla-policies` requires view, while editing needs SLA manage; a ticket may be opened only if the user’s effective scope includes it. See `PAGE_BY_PAGE_GUIDE.md` for exact route guard.

## Role-specific manual checklists

### Administrator

- [ ] Staff login and `/workspace` load.
- [ ] Create/activate branch, department and team; configure working hours/holiday.
- [ ] Invite/activate/deactivate a non-last-admin user and attach role/placement.
- [ ] Inspect/edit a custom role without altering system-role intent.
- [ ] Verify ticket catalogue, SLA policy, automation, channels and auth-policy pages are reachable.
- [ ] Inspect audit, retention, integrations and AI operations pages.

### Manager

- [ ] Can view any ticket and branch-scoped reports.
- [ ] Can assign/transfer work and manage staff/organisation within default permission set.
- [ ] Can configure SLA/automation/channels and report schedules/exports.
- [ ] Cannot assume administrator-only audit/data-protection/integration authority.

### Supervisor

- [ ] Can view only permitted department/team tickets, not arbitrary unrelated records.
- [ ] Can assign, collaborate through internal notes/tasks, change lifecycle and inspect SLA.
- [ ] Can inspect/reassign other users’ tasks and read department reports.
- [ ] Cannot perform manager-only user/role/SLA-rule management or ticket transfer capability by default.

### Agent

- [ ] Workspace shows own work and permitted queue after placement.
- [ ] Can create, claim/assign, reply, add internal note, watch and change allowed ticket status.
- [ ] Can create and complete own tasks; cannot browse others’ tasks by default.
- [ ] Can see customer context/notes/timeline but not customer edit/merge controls.
- [ ] Can author/edit, but not publish, knowledge; AI functions remain configuration-dependent.

### Viewer

- [ ] Can sign in and read only an owned ticket/message/attachment.
- [ ] Cannot see create/reply/status/customer/knowledge/report administrative actions.
- [ ] Own notifications can be read; attempting another ticket gives scope/permission denial.

### Portal customer

- [ ] Registration/verification/login separation works with local mail/token setup.
- [ ] Own ticket list/new/detail/public reply work.
- [ ] Staff internal notes and another customer’s URL never become visible.
- [ ] Public knowledge is available only when article is eligible/published; feedback obeys backend eligibility.

## Authentication lifecycle

Staff login uses `/auth/login`; policy-required 2FA goes to `/login/two-factor`, where challenge/recovery-code endpoints complete login. Password recovery uses `/forgot-password` and `/reset-password`; invitations use `/invitations/:token`. Staff account `/account` enrols/confirms/disables 2FA. Portal registration requires verification before portal login; portal session is separate. Logout endpoints exist for both surfaces. Tests: `LoginTest.php`, `PasswordResetTest.php`, `InvitationFlowTest.php`, `TwoFactorPolicyTest.php`, `PortalAuthTest.php`.
