<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Http\Resources\PortalTicketResource;
use App\Domains\Portal\Services\Visibility\PortalTicketScope;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalTicketController
{
    public function __construct(private PortalTicketScope $scope) {}

    public function index(Request $request): JsonResponse
    {
        $account = $request->user('portal');
        $tickets = $this->scope->forAccount($account)
            ->paginate(perPage: $request->input('per_page', 25));

        return response()->json([
            'data' => PortalTicketResource::collection($tickets->items()),
            'meta' => [
                'page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function show(Request $request, string $ticket): JsonResponse
    {
        $account = $request->user('portal');

        try {
            $ticket = $this->scope->findOrFail($account, $ticket);

            return response()->json([
                'data' => new PortalTicketResource($ticket),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Ticket not found',
                ],
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        // Stub: actual implementation uses CreateTicket action
        return response()->json(['message' => 'Not implemented yet'], 501);
    }
}
