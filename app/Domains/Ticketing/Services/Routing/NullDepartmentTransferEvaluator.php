<?php

namespace App\Domains\Ticketing\Services\Routing;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;

class NullDepartmentTransferEvaluator implements DepartmentTransferEvaluator
{
    public function evaluate(Ticket $ticket, Department $from, Department $to): void
    {
        // No-op: a real evaluator will replace this binding
    }
}
