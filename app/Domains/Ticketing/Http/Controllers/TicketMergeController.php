<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Actions\MergeTickets;
use App\Domains\Ticketing\Actions\SplitTicket;
use App\Domains\Ticketing\Http\Requests\MergeTicketsRequest;
use App\Domains\Ticketing\Http\Requests\SplitTicketRequest;
use App\Domains\Ticketing\Http\Resources\TicketResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class TicketMergeController extends Controller
{
    use AuthorizesRequests;

    public function merge(
        MergeTicketsRequest $request,
        Ticket $ticket,
        MergeTickets $merge,
    ) {
        $this->authorize('merge', $ticket);

        $target = Ticket::findOrFail($request->input('target'));
        $this->authorize('view', $target);

        $ticket = $merge($ticket, $target, $request->user());

        return ApiResponse::created(new TicketResource($ticket));
    }

    public function split(
        SplitTicketRequest $request,
        Ticket $ticket,
        SplitTicket $split,
    ) {
        $this->authorize('split', $ticket);

        $child = $split(
            $ticket,
            $request->user(),
            $request->input('subject'),
            $request->input('body'),
            $request->input('category'),
        );

        return ApiResponse::created(new TicketResource($child));
    }
}
