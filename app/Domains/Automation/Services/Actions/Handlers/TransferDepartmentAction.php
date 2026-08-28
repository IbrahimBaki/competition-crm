<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Actions\TransferTicketToDepartment;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class TransferDepartmentAction implements RuleAction
{
    public function __construct(
        private readonly TransferTicketToDepartment $transfer,
    ) {}

    public function type(): RuleActionType
    {
        return RuleActionType::TransferDepartment;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $deptId = $config['department_id'] ?? null;
        if (! $deptId || $ticket->department_id == $deptId) {
            return [];
        }

        $dept = Department::find($deptId);
        if (! $dept) {
            return [];
        }

        $before = ['department_id' => $ticket->department_id];
        $ticket = $this->transfer->handle($ticket, $dept, $actor, false, null);
        $after = ['department_id' => $ticket->department_id];

        return ['before' => $before, 'after' => $after];
    }
}
