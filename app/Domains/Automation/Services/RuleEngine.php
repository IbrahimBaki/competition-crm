<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\AutomationRuleExecution;
use App\Domains\Automation\Models\RuleExecutionOutcome;
use App\Domains\Automation\Models\RuleTrigger;
use App\Domains\Automation\Services\Actions\RuleActionRegistry;
use App\Domains\Automation\Services\Conditions\ConditionEvaluator;
use App\Domains\Automation\Services\Conditions\TicketFactProvider;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;

/**
 * Rule engine evaluating automation rules in deterministic order.
 *
 * Evaluation order:
 * 1. Load active rules for trigger where department_id is ticket's department OR null
 * 2. Sort: department-scoped before global, then priority ASC, then id ASC
 * 3. For each rule: build facts once, evaluate conditions, write execution row (matched/skipped) always, execute actions only if matched
 * 4. If matched rule has stop_on_match=true, stop; remaining rules produce no rows
 *
 * Idempotency key: sha1(rule_id|ticket_id|trigger|window) where window is:
 * - Scheduled with cooldown: floor of executed_at to cooldown bucket
 * - Scheduled without cooldown: ticket version
 * - Event triggers: ticket version
 *
 * Conflict resolution: last write wins, both executions logged.
 */
final class RuleEngine
{
    public function __construct(
        private readonly ConditionEvaluator $conditionEvaluator,
        private readonly TicketFactProvider $factProvider,
        private readonly RuleActionRegistry $actionRegistry,
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    /**
     * @return array<int, AutomationRuleExecution>
     */
    public function run(RuleTrigger $trigger, Ticket $ticket, ?User $actor = null, array $extraFacts = []): array
    {
        $ticket->load(['branch', 'currentDepartment']);
        $facts = $this->factProvider->provide($ticket);
        $facts = array_merge($facts, $extraFacts);

        $rules = AutomationRule::query()
            ->where('trigger', $trigger->value)
            ->where('is_active', true)
            ->where(function ($q) use ($ticket) {
                $q->where('department_id', $ticket->department_id)
                    ->orWhereNull('department_id');
            })
            ->orderBy('department_id', 'desc')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $executions = [];
        $now = CarbonImmutable::now('UTC');

        foreach ($rules as $rule) {
            $conditionResult = $this->conditionEvaluator->evaluate($rule->conditions, $facts);
            $matched = $conditionResult['matched'];
            $snapshot = $conditionResult['snapshot'];

            $window = $this->deriveWindow($rule, $trigger, $ticket, $now);
            $idempotencyKey = sha1("{$rule->id}|{$ticket->id}|{$trigger->value}|{$window}");

            $changes = [];
            $outcome = RuleExecutionOutcome::Skipped;

            if ($matched) {
                $outcome = RuleExecutionOutcome::Matched;
                $changes = $this->executeActions($rule, $ticket, $actor, $facts);

                $this->recordEvent->handle(
                    $ticket,
                    TicketEventType::AutomationRuleApplied,
                    null,
                    [
                        'rule_key' => $rule->key,
                        'rule_name' => $rule->name,
                        'changes' => $changes,
                    ]
                );
            }

            try {
                $execution = AutomationRuleExecution::create([
                    'automation_rule_id' => $rule->id,
                    'ticket_id' => $ticket->id,
                    'actor_id' => $actor?->id,
                    'trigger' => $trigger->value,
                    'outcome' => $outcome->value,
                    'condition_snapshot' => $snapshot,
                    'changes' => $changes,
                    'idempotency_key' => $idempotencyKey,
                    'executed_at' => $now,
                ]);

                $executions[] = $execution;
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'idempotency_key')) {
                    continue;
                }

                continue;
            }

            if ($matched && $rule->stop_on_match) {
                break;
            }
        }

        return $executions;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function executeActions(AutomationRule $rule, Ticket $ticket, ?User $actor, array $facts): array
    {
        $changes = [];

        foreach ($rule->actions as $actionConfig) {
            try {
                $actionType = $actionConfig['type'];
                $action = $this->actionRegistry->resolve($actionType);
                $change = $action->execute($ticket, $actionConfig, $actor);

                if ($change) {
                    $changes[] = array_merge(['action' => $actionType], $change);
                }
            } catch (\Throwable $e) {
                $changes[] = [
                    'action' => $actionConfig['type'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $changes;
    }

    private function deriveWindow(AutomationRule $rule, RuleTrigger $trigger, Ticket $ticket, CarbonImmutable $now): string
    {
        if ($trigger === RuleTrigger::Scheduled && $rule->cooldown_minutes) {
            $bucket = (int) ($now->getTimestamp() / ($rule->cooldown_minutes * 60));

            return (string) $bucket;
        }

        return (string) $ticket->version;
    }
}
