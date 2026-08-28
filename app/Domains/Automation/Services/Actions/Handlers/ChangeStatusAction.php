<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Ticketing\Actions\ChangeTicketStatus;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Models\User;

final class ChangeStatusAction implements RuleAction
{
    public function __construct(
        private readonly ChangeTicketStatus $changeStatus,
    ) {}

    public function type(): RuleActionType
    {
        return RuleActionType::ChangeStatus;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $statusId = $config['status_id'] ?? null;
        if (! $statusId || $ticket->status === $statusId) {
            return [];
        }

        $status = TicketStatusDefinition::find($statusId);
        if (! $status) {
            return [];
        }

        $before = ['status' => $ticket->status];
        $ticket = $this->changeStatus->handle($ticket, $status, $actor);
        $after = ['status' => $ticket->status];

        return ['before' => $before, 'after' => $after];
    }
}
