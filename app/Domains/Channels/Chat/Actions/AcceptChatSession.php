<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Exceptions\IllegalChatSessionTransitionException;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AcceptChatSession
{
    public function __construct(
        private readonly CreateTicket $createTicket,
    ) {}

    public function handle(ChatSession $session, User $agent): ChatSession
    {
        return DB::transaction(function () use ($session, $agent) {
            $session = $session->lockForUpdate()->fresh();

            if ($session->state !== ChatSessionState::Queued) {
                throw new IllegalChatSessionTransitionException;
            }

            $session->update([
                'state' => ChatSessionState::Active->value,
                'activated_at' => now(),
                'assigned_user_id' => $agent->uuid,
                'handled_by' => 'agent',
            ]);

            if (! $session->ticket_id) {
                $department = $session->department ?? $session->visitor->customer?->department;
                $subject = $session->subject ?? $session->initial_message ?? 'Chat Session';

                $ticket = $this->createTicket->handle(
                    customer: $session->visitor->customer,
                    department: $department,
                    priority: TicketPriority::Normal,
                    subject: $subject,
                    body: $session->initial_message ?? 'Chat conversation',
                );
                $session->update(['ticket_id' => $ticket->id]);
            }

            $session->events()->create(['type' => 'accepted', 'actor_user_id' => $agent->uuid]);

            return $session->refresh();
        });
    }
}
