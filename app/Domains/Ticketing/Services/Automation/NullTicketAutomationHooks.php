<?php

namespace App\Domains\Ticketing\Services\Automation;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Models\TicketStatus;
use App\Models\User;

final class NullTicketAutomationHooks implements TicketAutomationHooks
{
    public function ticketCreated(Ticket $ticket): void {}

    public function statusChanged(Ticket $ticket, TicketStatus $from, TicketStatus $to, ?User $actor): void {}

    public function priorityChanged(Ticket $ticket, ?User $actor): void {}

    public function departmentChanged(Ticket $ticket, Department $from, Department $to, ?User $actor): void {}

    public function messagePosted(Ticket $ticket, TicketMessage $message): void {}
}
