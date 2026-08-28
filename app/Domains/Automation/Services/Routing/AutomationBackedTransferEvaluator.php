<?php

namespace App\Domains\Automation\Services\Routing;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Services\Routing\DepartmentTransferEvaluator;

final class AutomationBackedTransferEvaluator implements DepartmentTransferEvaluator
{
    public function __construct(
        private readonly AutoAssignTicket $autoAssign,
    ) {}

    public function evaluate(Ticket $ticket, Department $from, Department $to): void
    {
        $this->autoAssign->assign($ticket);
    }
}
