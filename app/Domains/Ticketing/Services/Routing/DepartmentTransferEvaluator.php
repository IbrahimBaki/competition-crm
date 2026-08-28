<?php

namespace App\Domains\Ticketing\Services\Routing;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;

interface DepartmentTransferEvaluator
{
    /**
     * Re-evaluates the ticket against the routing and SLA rules
     * of the target department after a transfer.
     */
    public function evaluate(Ticket $ticket, Department $from, Department $to): void;
}
