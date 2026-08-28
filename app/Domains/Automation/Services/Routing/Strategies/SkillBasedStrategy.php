<?php

namespace App\Domains\Automation\Services\Routing\Strategies;

use App\Domains\Automation\Models\RoutingStrategy;
use App\Domains\Automation\Services\Routing\AssignmentStrategy;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class SkillBasedStrategy implements AssignmentStrategy
{
    public function __construct(
        private readonly LeastBusyStrategy $leastBusy,
    ) {}

    public function strategy(): RoutingStrategy
    {
        return RoutingStrategy::SkillBased;
    }

    public function pick(Ticket $ticket, Department $department): ?User
    {
        $eligible = $this->getEligibleAgents($department, $ticket);

        if ($eligible->isEmpty()) {
            return null;
        }

        return $this->leastBusy->pick($ticket, $department);
    }

    private function getEligibleAgents(Department $department, Ticket $ticket)
    {
        $requiredPermission = null;
        if ($ticket->category) {
            $requiredPermission = $ticket->category->required_permission;
        }

        if (! $requiredPermission) {
            return $department->users()
                ->where('is_active', true)
                ->where('anonymised_at', null)
                ->get();
        }

        return $department->users()
            ->where('is_active', true)
            ->where('anonymised_at', null)
            ->whereHas('roles.permissions', fn ($q) => $q->where('name', $requiredPermission))
            ->get();
    }
}
