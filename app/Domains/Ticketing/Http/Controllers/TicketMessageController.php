<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Actions\PostTicketMessage;
use App\Domains\Ticketing\Actions\RetryTicketMessage;
use App\Domains\Ticketing\Http\Requests\RetryTicketMessageRequest;
use App\Domains\Ticketing\Http\Requests\StoreTicketMessageRequest;
use App\Domains\Ticketing\Http\Resources\TicketMessageResource;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TicketMessageController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('viewAny', [TicketMessage::class, $ticket]);

        $spec = (new CollectionQuerySpec)
            ->withSorts(['created_at'])
            ->withFilters([
                'direction' => ['eq'],
                'channel' => ['eq'],
                'delivery_state' => ['eq'],
                'is_internal' => ['eq'],
            ]);

        $collectionQuery = new CollectionQuery($request, $spec);
        $query = $ticket->messages();

        if (! $this->userCanViewInternal($request->user(), $ticket)) {
            $query = $query->customerVisible();
        }

        $paginated = $collectionQuery->paginate($query);

        return ApiResponse::collection($paginated, $collectionQuery->meta())
            ->toResponse($request);
    }

    public function store(
        StoreTicketMessageRequest $request,
        Ticket $ticket,
        PostTicketMessage $postMessage,
    ): JsonResponse {
        $isInternal = $request->boolean('is_internal', false);

        $this->authorize('send', [TicketMessage::class, $ticket]);

        if ($isInternal) {
            $this->authorize('writeInternal', [TicketMessage::class, $ticket]);
        }

        $channel = MessageChannel::from($request->input('channel'));
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $request->user(),
            channel: $channel,
            body: $request->input('body'),
            isInternal: $isInternal,
            bodyFormat: $request->input('body_format', 'text'),
            attachmentUuids: $request->input('attachment_uuids', []),
        );

        return ApiResponse::item(new TicketMessageResource($message), 201)
            ->toResponse($request);
    }

    public function retry(
        RetryTicketMessageRequest $request,
        Ticket $ticket,
        TicketMessage $message,
        RetryTicketMessage $retryMessage,
    ): JsonResponse {
        if ($message->ticket_id !== $ticket->id) {
            abort(404);
        }

        $this->authorize('retry', $message);

        $message = $retryMessage->handle($message);

        return ApiResponse::item(new TicketMessageResource($message))
            ->toResponse($request);
    }

    public function deliveryEvents(
        Ticket $ticket,
        TicketMessage $message,
    ): JsonResponse {
        if ($message->ticket_id !== $ticket->id) {
            abort(404);
        }

        $this->authorize('viewAny', [TicketMessage::class, $ticket]);

        $events = $message->deliveryEvents;

        return ApiResponse::collection($events)
            ->toResponse(request());
    }

    private function userCanViewInternal($user, Ticket $ticket): bool
    {
        return $user && \Gate::allows('viewInternal', [TicketMessage::class, $ticket]);
    }
}
