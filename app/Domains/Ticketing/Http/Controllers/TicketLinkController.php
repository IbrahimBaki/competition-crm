<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Actions\LinkTickets;
use App\Domains\Ticketing\Actions\UnlinkTickets;
use App\Domains\Ticketing\Http\Requests\StoreTicketLinkRequest;
use App\Domains\Ticketing\Http\Resources\TicketLinkResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketLink;
use App\Domains\Ticketing\Models\TicketLinkRelation;
use App\Support\Http\ApiResponse;
use Illuminate\Routing\Controller;

class TicketLinkController extends Controller
{
    public function index(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $links = TicketLink::where('source_ticket_id', $ticket->id)->paginate();

        return ApiResponse::collection($links);
    }

    public function store(
        StoreTicketLinkRequest $request,
        Ticket $ticket,
        LinkTickets $linkTickets,
    ) {
        $this->authorize('link', $ticket);

        $target = Ticket::findOrFail($request->input('target'));
        $this->authorize('view', $target);

        $link = $linkTickets(
            $ticket,
            $target,
            TicketLinkRelation::from($request->input('relation')),
            $request->user(),
        );

        return ApiResponse::created(new TicketLinkResource($link));
    }

    public function destroy(
        Ticket $ticket,
        TicketLink $link,
        UnlinkTickets $unlinkTickets,
    ) {
        $this->authorize('link', $ticket);

        $unlinkTickets(
            $link->source,
            $link->target,
            $link->relation,
            \Auth::user(),
        );

        return ApiResponse::noContent();
    }
}
