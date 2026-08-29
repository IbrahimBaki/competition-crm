<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Http\Resources\PortalTicketMessageResource;
use App\Domains\Portal\Services\Visibility\PortalTicketScope;
use App\Domains\Ticketing\Models\MessageAuthorType;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Services\Automation\TicketAutomationHooks;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalTicketMessageController
{
    public function __construct(private PortalTicketScope $scope) {}

    public function index(Request $request, string $ticket): JsonResponse
    {
        $ticket = $this->scope->findOrFail($request->user('portal'), $ticket);
        $messages = $ticket->messages()
            ->customerVisible()
            ->with('attachments')
            ->orderBy('created_at')
            ->paginate((int) $request->input('per_page', 50));

        return response()->json([
            'data' => PortalTicketMessageResource::collection($messages->items()),
            'meta' => [
                'page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function store(Request $request, string $ticket, RecordTicketEvent $recordEvent, TicketAutomationHooks $automation): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);
        $account = $request->user('portal');
        $ticket = $this->scope->findOrFail($account, $ticket);
        $contact = $account->customer->contacts()->where('value', $account->email)->first();

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'direction' => MessageDirection::Inbound,
            'author_type' => MessageAuthorType::Customer,
            'author_customer_contact_id' => $contact?->id,
            'channel' => MessageChannel::Portal,
            'is_internal' => false,
            'body' => $validated['body'],
            'body_format' => 'text',
        ]);
        $recordEvent->handle($ticket, TicketEventType::MessagePosted, null, [
            'message_uuid' => $message->uuid,
            'channel' => MessageChannel::Portal->value,
            'is_internal' => false,
        ]);
        $automation->messagePosted($ticket->fresh(), $message);

        return response()->json(['data' => new PortalTicketMessageResource($message->load('attachments'))], 201);
    }
}
