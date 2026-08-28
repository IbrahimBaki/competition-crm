<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Actions\ChangeTicketStatus;
use App\Domains\Ticketing\Actions\MarkTicketAsSpam;
use App\Domains\Ticketing\Actions\ReopenTicket;
use App\Domains\Ticketing\Actions\RestoreTicketFromSpam;
use App\Domains\Ticketing\Http\Requests\ChangeTicketStatusRequest;
use App\Domains\Ticketing\Http\Requests\MarkTicketSpamRequest;
use App\Domains\Ticketing\Http\Requests\ReopenTicketRequest;
use App\Domains\Ticketing\Http\Requests\RestoreTicketFromSpamRequest;
use App\Domains\Ticketing\Http\Resources\TicketResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class TicketLifecycleController extends Controller
{
    use AuthorizesRequests;

    public function status(
        ChangeTicketStatusRequest $request,
        Ticket $ticket,
        ChangeTicketStatus $changeStatus,
    ) {
        $this->authorize('changeStatus', $ticket);

        $status = TicketStatusDefinition::findOrFail($request->input('status'));

        $ticket = $changeStatus($ticket, $status, $request->user(), $request->input('reason'));

        return ApiResponse::created(new TicketResource($ticket));
    }

    public function reopen(
        ReopenTicketRequest $request,
        Ticket $ticket,
        ReopenTicket $reopen,
    ) {
        $this->authorize('reopen', $ticket);

        $ticket = $reopen($ticket, $request->user(), $request->input('reason'));

        return ApiResponse::created(new TicketResource($ticket));
    }

    public function markSpam(
        MarkTicketSpamRequest $request,
        Ticket $ticket,
        MarkTicketAsSpam $markSpam,
    ) {
        $this->authorize('markSpam', $ticket);

        $ticket = $markSpam($ticket, $request->user(), $request->input('reason'));

        return ApiResponse::created(new TicketResource($ticket));
    }

    public function restoreFromSpam(
        RestoreTicketFromSpamRequest $request,
        Ticket $ticket,
        RestoreTicketFromSpam $restore,
    ) {
        $this->authorize('restoreFromSpam', $ticket);

        $ticket = $restore($ticket, $request->user(), $request->input('reason'));

        return ApiResponse::created(new TicketResource($ticket));
    }
}
