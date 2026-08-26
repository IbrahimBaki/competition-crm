<?php

namespace App\Domains\Automation\Http\Controllers;

use App\Domains\Automation\Actions\EscalateTicket;
use App\Domains\Automation\Http\Resources\AutomationRuleExecutionResource;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Http\Request;

class TicketEscalationController
{
    public function store(Request $request, Ticket $ticket, EscalateTicket $escalate)
    {
        $request->validate([
            'level' => 'required|integer|min:1',
            'reason' => 'required|string|min:3|max:2000',
        ]);

        $execution = $escalate->handle(
            $ticket,
            $request->integer('level'),
            $request->string('reason'),
            auth()->user()
        );

        return AutomationRuleExecutionResource::make($execution);
    }
}
