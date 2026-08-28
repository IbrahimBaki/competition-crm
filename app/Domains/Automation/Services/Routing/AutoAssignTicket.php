<?php

namespace App\Domains\Automation\Services\Routing;

use App\Domains\Automation\Models\AutomationRuleExecution;
use App\Domains\Automation\Models\RuleExecutionOutcome;
use App\Domains\Automation\Models\RuleTrigger;
use App\Domains\Automation\Services\Routing\Strategies\LeastBusyStrategy;
use App\Domains\Automation\Services\Routing\Strategies\ManualStrategy;
use App\Domains\Automation\Services\Routing\Strategies\RoundRobinStrategy;
use App\Domains\Automation\Services\Routing\Strategies\SkillBasedStrategy;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;

final class AutoAssignTicket
{
    public function __construct(
        private readonly ManualStrategy $manual,
        private readonly RoundRobinStrategy $roundRobin,
        private readonly LeastBusyStrategy $leastBusy,
        private readonly SkillBasedStrategy $skillBased,
    ) {}

    public function assign(Ticket $ticket): ?AutomationRuleExecution
    {
        $department = $ticket->currentDepartment;
        if (! $department) {
            return null;
        }

        $strategy = match ($department->routing_strategy->value) {
            'round_robin' => $this->roundRobin,
            'least_busy' => $this->leastBusy,
            'skill_based' => $this->skillBased,
            default => $this->manual,
        };

        $agent = $strategy->pick($ticket, $department);

        if ($agent) {
            $ticket->update(['assigned_user_id' => $agent->id, 'assigned_at' => now()]);

            return AutomationRuleExecution::create([
                'automation_rule_id' => 0,
                'ticket_id' => $ticket->id,
                'actor_id' => null,
                'trigger' => RuleTrigger::TicketCreated->value,
                'outcome' => RuleExecutionOutcome::Matched->value,
                'condition_snapshot' => [],
                'changes' => [['action' => 'auto_assign', 'before' => [], 'after' => ['assigned_user_id' => $agent->id]]],
                'idempotency_key' => sha1("{$ticket->id}|auto_assign|".$ticket->version),
                'executed_at' => CarbonImmutable::now('UTC'),
            ]);
        }

        return $this->applyFallback($ticket, $department);
    }

    private function applyFallback(Ticket $ticket, Department $department): ?AutomationRuleExecution
    {
        return match ($department->no_agent_fallback->value) {
            'leave_unassigned' => AutomationRuleExecution::create([
                'automation_rule_id' => 0,
                'ticket_id' => $ticket->id,
                'actor_id' => null,
                'trigger' => RuleTrigger::TicketCreated->value,
                'outcome' => RuleExecutionOutcome::Skipped->value,
                'condition_snapshot' => [],
                'changes' => [],
                'idempotency_key' => sha1("{$ticket->id}|fallback_unassigned|".$ticket->version),
                'executed_at' => CarbonImmutable::now('UTC'),
            ]),
            default => null,
        };
    }
}
