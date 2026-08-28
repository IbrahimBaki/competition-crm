<?php

namespace App\Domains\Automation\Services\Routing;

use App\Domains\Automation\Models\RoutingStrategy;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

interface AssignmentStrategy
{
    public function strategy(): RoutingStrategy;

    public function pick(Ticket $ticket, Department $department): ?User;
}
