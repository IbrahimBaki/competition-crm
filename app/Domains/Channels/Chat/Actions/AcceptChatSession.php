<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Exceptions\ChatAgentAtCapacityException;
use App\Domains\Channels\Chat\Exceptions\ChatVisitorContactRequiredException;
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
            // lockForUpdate() on a model instance returns a Builder scoped by
            // Eloquent's magic __call, which has no fresh() method — this threw
            // BadMethodCallException on every accept. Re-query the row instead.
            $session = ChatSession::whereKey($session->getKey())->lockForUpdate()->firstOrFail();

            if ($session->state !== ChatSessionState::Queued) {
                throw new IllegalChatSessionTransitionException;
            }

            // channels.chat.max_concurrent_per_agent was configured but never
            // enforced anywhere; without this an agent can accept unlimited chats.
            $limit = (int) config('channels.chat.max_concurrent_per_agent', 3);
            $active = ChatSession::where('assigned_user_id', $agent->uuid)
                ->where('state', ChatSessionState::Active->value)
                ->count();

            if ($active >= $limit) {
                throw new ChatAgentAtCapacityException;
            }

            $session->update([
                'state' => ChatSessionState::Active->value,
                'activated_at' => now(),
                'assigned_user_id' => $agent->uuid,
                'handled_by' => 'agent',
            ]);

            if (! $session->ticket_id) {
                // The widget's visitor-info step is explicitly optional (see
                // docs/ui/13-public-surfaces.md), so a session can reach here
                // with no email/phone ever captured — and Customer requires at
                // least one contact, so no Customer record exists to attach a
                // ticket to. CreateTicket requires a non-null Customer, so this
                // used to fail with a raw TypeError instead of a clean 422.
                if (! $session->visitor->customer) {
                    throw new ChatVisitorContactRequiredException;
                }

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
