<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Actions\UnwatchTicket;
use App\Domains\Ticketing\Actions\WatchTicket;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

readonly class TicketWatcherController
{
    use AuthorizesRequests;

    public function __construct(
        private WatchTicket $watchAction,
        private UnwatchTicket $unwatchAction,
    ) {}

    public function index(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $watchers = $ticket->watchers()
            ->select('uuid', 'name')
            ->get();

        return ApiResponse::item(['watchers' => $watchers])
            ->toResponse(request());
    }

    public function store(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $this->watchAction->handle($ticket, auth()->user());

        return ApiResponse::item(['watched' => true], 201)
            ->toResponse(request());
    }

    public function destroy(Ticket $ticket, User $user): JsonResponse
    {
        $this->authorize('view', $ticket);

        $this->unwatchAction->handle($ticket, $user);

        return response()->noContent();
    }
}
