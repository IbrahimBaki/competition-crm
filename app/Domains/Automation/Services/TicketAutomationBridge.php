<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Models\RuleTrigger;
use App\Domains\Automation\Services\Routing\AutoAssignTicket;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Models\TicketStatus;
use App\Domains\Ticketing\Services\Automation\TicketAutomationHooks;
use App\Models\User;

final class TicketAutomationBridge implements TicketAutomationHooks
{
    public function __construct(
        private readonly RuleEngine $engine,
        private readonly AutoAssignTicket $autoAssign,
    ) {}

    public function ticketCreated(Ticket $ticket): void
    {
        $this->autoAssign->assign($ticket);
        $this->engine->run(RuleTrigger::TicketCreated, $ticket);
    }

    public function statusChanged(Ticket $ticket, TicketStatus $from, TicketStatus $to, ?User $actor): void
    {
        $this->engine->run(RuleTrigger::StatusChanged, $ticket, $actor, [
            'previous_status' => $from->id,
        ]);
    }

    public function priorityChanged(Ticket $ticket, ?User $actor): void
    {
        $this->engine->run(RuleTrigger::PriorityChanged, $ticket, $actor);
    }

    public function departmentChanged(Ticket $ticket, Department $from, Department $to, ?User $actor): void
    {
        $this->engine->run(RuleTrigger::DepartmentTransferred, $ticket, $actor, [
            'previous_department' => $from->id,
        ]);
    }

    public function messagePosted(Ticket $ticket, TicketMessage $message): void
    {
        $actor = $message->actor_type === 'user' ? User::find($message->actor_id) : null;
        $this->engine->run(RuleTrigger::MessagePosted, $ticket, $actor, [
            'message_channel' => $message->channel,
        ]);
    }
}
