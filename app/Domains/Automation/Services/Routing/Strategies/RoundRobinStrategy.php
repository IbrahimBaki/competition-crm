<?php

namespace App\Domains\Automation\Services\Routing\Strategies;

use App\Domains\Automation\Models\AutomationRuleExecution;
use App\Domains\Automation\Models\RoutingStrategy;
use App\Domains\Automation\Services\Routing\AssignmentStrategy;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class RoundRobinStrategy implements AssignmentStrategy
{
    public function strategy(): RoutingStrategy
    {
        return RoutingStrategy::RoundRobin;
    }

    public function pick(Ticket $ticket, Department $department): ?User
    {
        $eligible = $this->getEligibleAgents($department);

        if ($eligible->isEmpty()) {
            return null;
        }

        $lastExecution = AutomationRuleExecution::where('ticket_id', '!=', $ticket->id)
            ->whereHas('rule', fn ($q) => $q->where('department_id', $department->id))
            ->orderBy('executed_at', 'desc')
            ->first();

        $lastAssignedId = null;
        if ($lastExecution) {
            $changes = $lastExecution->changes;
            foreach ($changes as $change) {
                if ($change['action'] === 'assign' && isset($change['after']['assigned_user_id'])) {
                    $lastAssignedId = $change['after']['assigned_user_id'];
                    break;
                }
            }
        }

        $eligibleIds = $eligible->pluck('id')->toArray();
        if (! $lastAssignedId || ! in_array($lastAssignedId, $eligibleIds)) {
            return $eligible->first();
        }

        $lastIndex = array_search($lastAssignedId, $eligibleIds);
        $nextIndex = ($lastIndex + 1) % count($eligibleIds);

        return User::find($eligibleIds[$nextIndex]);
    }

    private function getEligibleAgents(Department $department)
    {
        return $department->users()
            ->where('is_active', true)
            ->where('anonymised_at', null)
            ->orderBy('id')
            ->get();
    }
}
