<?php

namespace App\Domains\Automation\Services\Routing\Strategies;

use App\Domains\Automation\Models\RoutingStrategy;
use App\Domains\Automation\Services\Routing\AssignmentStrategy;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class ManualStrategy implements AssignmentStrategy
{
    public function strategy(): RoutingStrategy
    {
        return RoutingStrategy::Manual;
    }

    public function pick(Ticket $ticket, Department $department): ?User
    {
        return null;
    }
}
