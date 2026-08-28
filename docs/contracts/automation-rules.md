# Automation Rules Engine

## Triggers

Rules are evaluated when one of these events occurs:

- `ticket_created` — new ticket entered the system
- `status_changed` — ticket status transitioned
- `priority_changed` — ticket priority raised or lowered
- `department_transferred` — ticket transferred to another department
- `message_posted` — message added to conversation
- `sla_warning_raised` — SLA clock approaching breach
- `sla_breached` — SLA clock expired
- `scheduled` — periodic sweep (every 10 minutes)
- `manual_escalation` — agent escalates via API

## Condition Node Grammar

A condition node evaluates facts against criteria:

```json
{
  "field": "priority",
  "operator": "in",
  "value": ["high", "urgent"]
}
```

Supported operators:
- `eq` — equality
- `neq` — inequality
- `in` — value in array
- `not_in` — value not in array
- `gt`, `gte`, `lt`, `lte` — numeric comparison
- `is_null`, `is_not_null` — null checks

Groups combine conditions with Boolean logic:

```json
{
  "all": [
    { "field": "status", "operator": "eq", "value": "open" },
    { "field": "age_business_minutes", "operator": "gt", "value": 480 }
  ]
}
```

```json
{
  "any": [
    { "field": "priority", "operator": "eq", "value": "urgent" },
    { "field": "sla_breached", "operator": "eq", "value": true }
  ]
}
```

## Evaluation Order

1. Load active rules for the trigger where `department_id` is the ticket's department **or** `null` (global)
2. Sort: department-scoped rules **before** global rules, then `priority` ASC, then `id` ASC
3. For each rule in order:
   - Build ticket facts **once** for the entire sweep
   - Evaluate conditions against facts
   - Write execution row (outcome: matched/skipped/failed) **always**
   - Execute actions only if matched
   - Record ticket event if matched
4. If a matched rule has `stop_on_match=true`, stop; remaining rules are not evaluated

## Conflict Resolution

When multiple matched rules modify the same field, **last write wins**. Both executions are logged in full.

Example: Rule A raises priority to high, then Rule B (with lower priority value, evaluated later) raises it to urgent → ticket ends at urgent, both logged.

## Idempotency

Each execution has a unique `idempotency_key` derived from:
- `sha1(rule_id|ticket_id|trigger|window)`

Where `window` is:

- **Scheduled trigger with cooldown**: floor of `executed_at` to cooldown bucket (e.g., 60-minute buckets)
- **Scheduled trigger without cooldown**: ticket's current version
- **Event trigger**: ticket's version at trigger time

Repeated executions (sweep re-run on same ticket/version) hit the unique constraint and are skipped gracefully.

## Facts Available in Conditions

- `status` — ticket status string
- `priority` — priority value (high/medium/low/urgent)
- `department_id` — department ID
- `category_id` — category ID
- `assignee_id` — assigned user ID (null if unassigned)
- `tags` — array of tag names
- `age_business_minutes` — elapsed working time since created
- `minutes_since_last_customer_message` — working minutes since customer last replied
- `minutes_since_last_agent_message` — working minutes since agent last replied
- `sla_position` — remaining minutes until SLA breach (null if no clock)
- `sla_breached` — true if SLA clock is breached

## Actions

Available action types:

- `assign` — assign to a specific user (set `user_id` in config)
- `reassign` — reassign from current agent to another (set `user_id`)
- `transfer_department` — move ticket to department (set `department_id`)
- `raise_priority` — escalate priority if not already at or above target (set `priority`)
- `change_status` — set status (set `status_id`)
- `add_tag` — append tag (set `tag`)
- `notify` — record notification intent (set `channel` and `recipient`)
- `escalate` — escalate to a level with explicit outcomes (set `level`, `reason`, `outcomes`)

Actions are executed in array order. If one fails, the error is logged and execution continues to the next action.
