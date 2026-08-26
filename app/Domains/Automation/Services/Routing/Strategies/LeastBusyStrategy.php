<?php

namespace App\Domains\Automation\Services\Routing\Strategies;

use App\Domains\Automation\Models\RoutingStrategy;
use App\Domains\Automation\Services\Routing\AssignmentStrategy;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class LeastBusyStrategy implements AssignmentStrategy
{
    public function strategy(): RoutingStrategy
    {
        return RoutingStrategy::LeastBusy;
    }

    public function pick(Ticket $ticket, Department $department): ?User
    {
        $eligible = $this->getEligibleAgents($department);

        if ($eligible->isEmpty()) {
            return null;
        }

        $terminalStatuses = TicketStatus::where('lifecycle_type', 'closed')->pluck('id')->toArray();

        $agentLoad = $eligible->mapWithKeys(function (User $user) use ($terminalStatuses) {
            $openCount = Ticket::where('assigned_user_id', $user->id)
                ->whereNotIn('ticket_status_id', $terminalStatuses)
                ->count();

            return [$user->id => ['user' => $user, 'open_count' => $openCount]];
        });

        $sorted = $agentLoad->sortBy(fn ($item) => [$item['open_count'], $item['user']->id]);

        return $sorted->first()['user'];
    }

    private function getEligibleAgents(Department $department): Collection
    {
        return $department->users()
            ->where('is_active', true)
            ->where('anonymised_at', null)
            ->orderBy('id')
            ->get();
    }
}
