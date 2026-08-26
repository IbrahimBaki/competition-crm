<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Exceptions\TicketAlreadyInDepartmentException;
use App\Domains\Ticketing\Exceptions\TicketIsReadOnlyException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\Concurrency\TicketVersionGuard;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\Routing\DepartmentTransferEvaluator;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferTicketToDepartment
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly TicketVersionGuard $versionGuard,
        private readonly DepartmentTransferEvaluator $evaluator,
    ) {}

    public function handle(Ticket $ticket, Department $department, ?User $actor = null, bool $keepAssignee = false, ?int $expectedVersion = null): Ticket
    {
        if ($ticket->isMerged() || $ticket->isSpam()) {
            throw new TicketIsReadOnlyException('Ticket is read-only');
        }

        $this->versionGuard->assert($ticket, $expectedVersion);

        if ($department->id === $ticket->department_id) {
            throw new TicketAlreadyInDepartmentException('Ticket is already in this department');
        }

        if (! $department->is_active) {
            throw new TicketAlreadyInDepartmentException('Target department is inactive');
        }

        $fromDepartment = $ticket->department;
        $previousAssigneeId = $ticket->assigned_user_id;
        $previousAssigneeUuid = null;
        if ($previousAssigneeId) {
            $previousAssignee = User::find($previousAssigneeId);
            $previousAssigneeUuid = $previousAssignee?->uuid;
        }

        $attributes = [
            'department_id' => $department->id,
        ];

        if (! $keepAssignee) {
            $attributes['assigned_user_id'] = null;
            $attributes['assigned_at'] = null;
        }

        $ticket = $this->versionGuard->bump($ticket, $attributes, $expectedVersion);

        $this->recordEvent->handle($ticket, TicketEventType::TransferredToDepartment, $actor, [
            'from_department_uuid' => $fromDepartment?->uuid,
            'to_department_uuid' => $department->uuid,
            'previous_user_uuid' => $previousAssigneeUuid,
        ]);

        if (! $keepAssignee && $previousAssigneeId !== null) {
            $this->recordEvent->handle($ticket, TicketEventType::Unassigned, $actor, [
                'previous_user_uuid' => $previousAssigneeUuid,
            ]);
        }

        DB::transaction(fn () => $this->evaluator->evaluate($ticket, $fromDepartment, $department));

        return $ticket->fresh();
    }
}
