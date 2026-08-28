<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Exceptions\EscalationReasonRequiredException;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\AutomationRuleExecution;
use App\Domains\Automation\Models\RuleExecutionOutcome;
use App\Domains\Automation\Models\RuleTrigger;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;
use Carbon\CarbonImmutable;

final class EscalateTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(
        Ticket $ticket,
        int $level,
        string $reason,
        ?User $actor = null,
        ?AutomationRule $rule = null,
    ): AutomationRuleExecution {
        if (blank(trim($reason))) {
            throw new EscalationReasonRequiredException('Escalation reason is required');
        }

        if ($ticket->escalation_level && $ticket->escalation_level >= $level) {
            return AutomationRuleExecution::create([
                'automation_rule_id' => $rule?->id ?? 0,
                'ticket_id' => $ticket->id,
                'actor_id' => $actor?->id,
                'trigger' => RuleTrigger::ManualEscalation->value,
                'outcome' => RuleExecutionOutcome::Skipped->value,
                'condition_snapshot' => [],
                'changes' => [],
                'reason' => $reason,
                'idempotency_key' => sha1("{$rule?->id}|{$ticket->id}|escalate|{$level}"),
                'executed_at' => CarbonImmutable::now('UTC'),
            ]);
        }

        $before = ['escalation_level' => $ticket->escalation_level];
        $ticket->update(['escalation_level' => $level]);
        $after = ['escalation_level' => $level];

        $this->recordEvent->handle(
            $ticket,
            TicketEventType::Escalated,
            $actor,
            [
                'level' => $level,
                'reason' => $reason,
            ]
        );

        return AutomationRuleExecution::create([
            'automation_rule_id' => $rule?->id ?? 0,
            'ticket_id' => $ticket->id,
            'actor_id' => $actor?->id,
            'trigger' => RuleTrigger::ManualEscalation->value,
            'outcome' => RuleExecutionOutcome::Matched->value,
            'condition_snapshot' => [],
            'changes' => [['action' => 'escalate', 'before' => $before, 'after' => $after]],
            'reason' => $reason,
            'idempotency_key' => sha1("{$rule?->id}|{$ticket->id}|escalate|{$level}|".$ticket->version),
            'executed_at' => CarbonImmutable::now('UTC'),
        ]);
    }
}
