<?php

namespace App\Domains\Channels\Chat\Services\Transcript;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionEventType;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\TicketPriority;
use Illuminate\Support\Facades\DB;

final class PersistChatTranscript
{
    public function __construct(
        private readonly CreateTicket $createTicket,
    ) {}

    public function handle(ChatSession $session): void
    {
        if ($session->transcript_persisted_at) {
            return;
        }

        DB::transaction(function () use ($session) {
            if (! $session->ticket_id) {
                $department = $session->department ?? $session->visitor->customer?->department;
                // No agent ever engaged this session (it was abandoned/ended
                // before accept), so there is nobody to prompt for contact
                // info — skip ticket creation exactly like the missing-
                // department case above, rather than crash on a null customer.
                if (! $department || ! $session->visitor->customer) {
                    return;
                }

                $subject = $session->subject ?? 'Chat Session '.$session->uuid;
                $ticket = $this->createTicket->handle(
                    customer: $session->visitor->customer,
                    department: $department,
                    priority: TicketPriority::Normal,
                    subject: $subject,
                    body: $session->initial_message ?? 'Chat session transcript',
                );
                $session->update(['ticket_id' => $ticket->id]);
            }

            $messages = $session->messages()->whereNull('ticket_message_id')->get();
            foreach ($messages as $msg) {
                $ticketMessage = $session->ticket->messages()->create([
                    'direction' => $msg->author_type === 'visitor' ? MessageDirection::Inbound : MessageDirection::Outbound,
                    'author_type' => $msg->author_type === 'visitor' ? 'customer' : $msg->author_type,
                    'channel' => MessageChannel::Chat,
                    'body' => $msg->body,
                    'delivery_state' => MessageDeliveryState::Delivered,
                    'sent_at' => $msg->sent_at,
                ]);

                $msg->update(['ticket_message_id' => $ticketMessage->id]);
            }

            $session->update(['transcript_persisted_at' => now()]);
            $session->events()->create([
                'type' => ChatSessionEventType::TranscriptPersisted->value,
            ]);
        });
    }
}
