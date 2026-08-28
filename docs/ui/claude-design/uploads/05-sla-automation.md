# 05. SLA & Automation

**Domains**: Sla, Automation  
**Surface**: Staff CRM only  
**Permissions**: `sla.*`, `automation.*`, `tickets.escalate`

---

## SLA Policies Screen

**Route**: `/admin/sla/policies`  
**Purpose**: Manage SLA policies (response + resolution targets)  
**Permission**: `sla.policies.manage`  

**List of Policies**:
| Name | Is Default | Is Active | Warning % | Actions |
|---|---|---|---|---|
| Standard (مستند) | ✓ | ✓ | 80 | Edit / Delete |
| Premium (مميز) | — | ✓ | 75 | Edit / Delete |
| [+ Add Policy] | — | — | — | — |

**Actions** (per policy):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to policy edit | `/admin/sla/policies/{id}` | (same) | N/A |
| Delete | DELETE | `DELETE /sla/policies/{policy}` | (same) | Yes (if no tickets use it) |

**Related endpoints**:
- `GET /sla/policies` (list)

---

## SLA Policy Edit Screen

**Route**: `/admin/sla/policies/new` or `/admin/sla/policies/{id}`  
**Purpose**: Create/edit SLA policy + define targets  
**Permission**: `sla.policies.manage`  

**Section 1: Basic Info**

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Policy label (ar/en) |
| Is Default | checkbox | No | No | Auto-assign to new tickets |
| Is Active | checkbox | No | No | Deactivate to stop using |
| Warning Threshold % | number | Yes | No | At what % of target time to show warning badge (e.g., 80) |

---

**Section 2: SLA Targets Matrix**

**Table** (priority × category × service_tier → target minutes):

```
Target Type: First Response (minutes within which agent must reply)
             Standard  Premium  VIP
Technical    120       60       30
Billing      240       120      60
General      480       240      120

Target Type: Resolution (minutes within which ticket must close)
             Standard  Premium  VIP
Technical    1440      480      240
Billing      2880      1440     480
General      4320      2880     1440
```

**Row headers** (Priority: Low, Normal, High, Urgent × Category: from ticket categories)  
**Column headers** (Service Tier: Standard, Priority, VIP)  
**Cell editor**: click cell to edit (input minutes), shows "X hours Y minutes" helper text below  

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save Policy | PATCH policy | `PATCH /sla/policies/{policy}` | (same) | No |
| Reset to Defaults | Populate matrix with standard values | (modal confirm) | (same) | No |

**Related endpoints**:
- `PATCH /sla/policies/{policy}` (update policy + targets)

**Notes**:
- Business hours applied per ticket's branch (branch.timezone, branch.working_hours, branch.holidays)
- If ticket reclassified, SLA recalculates based on new category/priority + policy targets
- If ticket paused, SLA countdown stops until resumed

---

## SLA Breach Report

**Route**: `/admin/sla/breaches` or widget on dashboard  
**Purpose**: View tickets with breached SLA  
**Permission**: `sla.policies.view`  

**Table**:
| Reference | Customer | Category | Priority | SLA Policy | Target | Breached At | Status | Actions |
|---|---|---|---|---|---|---|---|---|
| TKT-001 | Ahmed | Technical | High | Premium | 1h (Resolution) | 2h ago | Resolved | Detail |
| TKT-002 | Sarah | Billing | Normal | Standard | 4h (Response) | 30m ago | Open | Assign |

**Filters**:
- Policy, priority, category, branch (if applicable)
- Date range (breached date)
- Status (open, pending, resolved, closed — some are already closed despite breach)

**Related endpoints**:
- `GET /tickets?filter[sla_breach]=true` (implied filter to get breached tickets)

**Notes**:
- Breached but closed: shown as informational (SLA target was missed, but customer issue resolved)
- Breached and open: actionable — may need escalation or priority boost

---

## SLA Reset Action (Admin)

**Route**: (Action button on ticket detail if `sla.reset` permission)  
**Purpose**: Manually reset SLA clock for a ticket  
**Permission**: `sla.reset`  

**Action**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Reset SLA | Modal confirm + POST reset | (TBD endpoint, likely POST /tickets/{ticket}/sla/reset) | (same) | Yes |

**Modal**:
- Confirm "Reset SLA for ticket TKT-001? Clock will restart from now."
- Optional: reason text field (audit trail)

**Result**:
- Both first_response and resolution clocks restart
- Audit entry recorded (actor, timestamp, reason)

---

## Automation Rules Screen

**Route**: `/admin/automation/rules`  
**Purpose**: List and manage workflow automation rules  
**Permission**: `automation.rules.manage`  

**List of Rules** (ordered by priority):
| Priority | Name | Trigger | Status | Actions |
|---|---|---|---|---|
| 1 | Auto-Assign Support (تعيين تلقائي) | ticket_created | Active ✓ | Edit / Disable / Delete |
| 2 | Escalate High Priority | priority_changed | Active ✓ | — |
| 3 | Reassign Unresolved | sla_warning_raised | Disabled | Re-enable / Delete |

**Actions** (per rule):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to rule edit | `/admin/automation/rules/{id}` | (same) | N/A |
| Enable/Disable | Toggle active flag | `PATCH /automation/rules/{rule}` (set is_active) | (same) | No |
| Delete | DELETE | `DELETE /automation/rules/{rule}` | (same) | Yes |
| View Executions | Link to rule execution log (filtered by rule) | `/admin/automation/executions?filter[rule_id]={id}` | (same) | N/A |

**Related endpoints**:
- `GET /automation/rules` (list)

---

## Automation Rule Edit Screen

**Route**: `/admin/automation/rules/new` or `/admin/automation/rules/{id}`  
**Purpose**: Create/edit workflow automation rule with visual builder  
**Permission**: `automation.rules.manage`  

**Section 1: Basic Info**

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Rule label (ar/en) |
| Key | string | Yes | No | Unique identifier (auto-generated or manual) |
| Department | select | No | No | If set, rule applies only to tickets in this department |
| Priority | number | Yes | No | Execution order (1–100); lower runs first |
| Stop on Match | checkbox | No | No | If checked, stop processing further rules after this one matches |
| Is Active | checkbox | No | No | Deactivate without deleting |

---

**Section 2: Trigger** (Visual selector)

**Trigger** (single choice):
- Ticket Created
- Ticket Updated
- Status Changed
- Priority Changed
- Department Transferred
- Message Posted
- SLA Warning Raised
- SLA Breached
- Scheduled (cron-like: daily, weekly, monthly)
- Manual Escalation

**Related endpoints**:
- Trigger options are enums, hardcoded in UI (see `RuleTrigger` enum in domain)

---

**Section 3: Conditions** (AND logic — all must be true)

**Visual builder** (rows of conditions):
```
[+ Add Condition]

Condition 1:  [Field: Priority]  [Operator: equals]  [Value: high]  [remove]
Condition 2:  [Field: Category]  [Operator: in]      [Values: Technical, Billing]  [remove]
Condition 3:  [Field: Created At]  [Operator: >]      [Value: 2026-08-20]  [remove]
```

**Supported conditions**:
- Priority (equals, not_equals)
- Status (equals, in)
- Category (equals, in)
- Created After / Before (date)
- Service Tier (equals)
- Customer Tag (in)

**Operators**: equals, not_equals, in, not_in, >, <, >=, <=, contains, etc.

---

**Section 4: Actions** (Ordered, sequential)

**Visual builder** (ordered list of actions):
```
[+ Add Action]

Action 1:  [Type: Assign]  [Agent: random from department]  [remove]  [↑][↓]
Action 2:  [Type: Add Tag]  [Tag: urgent]  [remove]  [↑][↓]
Action 3:  [Type: Change Status]  [Status: pending]  [remove]  [↑][↓]
```

**Supported actions**:
- **Assign** (agent, round_robin, least_busy, skill_based; or department if `NoAgentFallback` = escalate/queue)
- **Reassign** (if currently assigned, replace assignee)
- **Transfer Department** (move to target department)
- **Raise Priority** (increment priority level, e.g., normal → high)
- **Change Status** (set to specific status)
- **Add Tag** (add tag to ticket)
- **Notify** (send notification to agent/manager)
- **Escalate** (raise escalation level, trigger escalation_target type selection)

**Action Parameters** (vary per type):
- Assign: target (agent_id, round_robin, least_busy, skill_based), NoAgentFallback (leave_unassigned, department_queue, escalate), CooldownMinutes (don't reassign if already assigned within X minutes)
- Notify: recipient (agent, manager, department, role), channel (in_app, mail)
- Escalate: target_type (user, role, department), cooldown

---

**Section 5: Advanced**

**Fields**:
| Field | Type | Notes |
|---|---|---|
| Escalation Level | number | Level in escalation chain (if rule includes escalation) |
| Cooldown Minutes | number | Don't re-execute rule on same ticket within X minutes |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save Rule | POST/PATCH rule | `POST /automation/rules` or `PATCH /automation/rules/{rule}` | (same) | No |
| Test Rule | Dry-run: show tickets that would match | (modal) | (same) | N/A |
| Cancel | Go back | — | N/A | N/A |

**Related endpoints**:
- `POST /automation/rules`, `PATCH /automation/rules/{rule}` (create/update)
- (Condition/action JSON stored in `conditions` and `actions` columns per `docs/contracts/automation-rules.md`)

**Notes**:
- Rules are evaluated server-side on ticket events
- Execution audit: see Automation Execution Log below
- Conditions must evaluate to true for all rows; if any false, rule doesn't execute
- Actions execute in order; some actions may affect subsequent actions (e.g., assign, then notify assignee)

---

## Automation Rule Execution Log

**Route**: `/admin/automation/executions`  
**Purpose**: Audit trail of rule executions  
**Permission**: `automation.executions.view`  

**Table**:
| Timestamp | Rule | Trigger | Ticket | Status | Executed Actions | Reason |
|---|---|---|---|---|---|---|
| 2026-08-27 10:30 | Auto-Assign Support | ticket_created | TKT-042 | success | assign, notify | Matched all conditions |
| 2026-08-27 10:29 | Escalate High Priority | priority_changed | TKT-041 | success | raise_priority, notify | — |
| 2026-08-27 10:15 | Test Rule | — | TKT-040 | matched | (none — dry-run) | Dry-run test |
| 2026-08-27 09:00 | Auto-Assign Support | ticket_created | TKT-039 | skipped | — | Rule disabled |

**Filters**:
- Rule name/ID
- Trigger type
- Status (success, skipped, failed, matched_dry_run)
- Date range
- Ticket reference (search)

**Related endpoints**:
- `GET /automation/executions` (list with pagination/filter)

**Notes**:
- Useful for debugging rule behavior
- Dry-run test results also logged (status = matched_dry_run)

---

## Ticket Escalation Modal (inline on ticket detail)

**Route**: (Modal, accessible from ticket detail or via escalation action)  
**Purpose**: Manually escalate ticket to higher-level support  
**Permission**: `tickets.escalate`  

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Escalation Level | select | Yes | Level 1 → Manager, Level 2 → Director, Level 3 → VIP Team |
| Escalation Target | select | Yes | User/role/department (determined by escalation_target_type config) |
| Reason | textarea | Yes | Why escalating? (stored in audit trail) |
| Add Internal Note | checkbox | No | Auto-create internal note with reason |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Escalate | POST escalate | `POST /tickets/{ticket}/escalate` | (same) | Yes |

**Related endpoints**:
- `POST /tickets/{ticket}/escalate` (with escalation_level, target, reason)

**Notes**:
- Automation rules can also trigger escalation (see Actions section above)
- Manual escalation is also an audit-logged event

---

## Navigation Map

- **Sidebar: Admin > SLA & Automation**
  - **SLA Policies** → (list) → (click policy) → **SLA Policy Edit** (matrix builder)
  - **SLA Breaches** → (report, filter by policy/date)
  - **Automation Rules** → (list, ordered by priority) → (click rule) → **Rule Edit** (visual builder)
    - **View Executions** → **Execution Log** (filtered by rule)
  - **Execution Log** → (global view of all rule executions)
- **Ticket Detail** → [Escalate] button → **Escalation Modal**

---

**Next**: [06-channels.md](06-channels.md)
