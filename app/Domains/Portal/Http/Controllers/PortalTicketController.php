<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Organisation\Models\Department;
use App\Domains\Portal\Http\Resources\PortalTicketResource;
use App\Domains\Portal\Services\Visibility\PortalTicketScope;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Models\MessageAuthorType;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Domains\Ticketing\Services\Automation\TicketAutomationHooks;
use App\Domains\Ticketing\Services\RecordTicketEvent;
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

    public function store(Request $request, CreateTicket $createTicket, RecordTicketEvent $recordEvent, TicketAutomationHooks $automation): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $account = $request->user('portal');
        $department = Department::query()->where('is_active', true)->orderBy('code')->first();

        abort_unless($department, 503, 'No support department is currently available.');

        $ticket = $createTicket->handle(
            customer: $account->customer,
            department: $department,
            priority: TicketPriority::Normal,
            subject: $validated['subject'],
            body: $validated['message'],
        );

        $contact = $account->customer->contacts()->where('value', $account->email)->first();
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'direction' => MessageDirection::Inbound,
            'author_type' => MessageAuthorType::Customer,
            'author_customer_contact_id' => $contact?->id,
            'channel' => MessageChannel::Portal,
            'is_internal' => false,
            'body' => $validated['message'],
            'body_format' => 'text',
        ]);
        $recordEvent->handle($ticket, TicketEventType::MessagePosted, null, [
            'message_uuid' => $message->uuid,
            'channel' => MessageChannel::Portal->value,
            'is_internal' => false,
        ]);
        $automation->messagePosted($ticket->fresh(), $message);

        return response()->json(['data' => new PortalTicketResource($ticket->fresh())], 201);
    }
}
