<?php

namespace App\Domains\Portal\Actions;

use App\Domains\Portal\Models\TicketFeedbackInvitation;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Support\Str;

class IssueFeedbackInvitation
{
    public function handle(Ticket $ticket): ?TicketFeedbackInvitation
    {
        $existing = TicketFeedbackInvitation::where('ticket_id', $ticket->id)->first();

        if ($existing) {
            return null;
        }

        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        $invitation = TicketFeedbackInvitation::create([
            'ticket_id' => $ticket->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays(config('portal.feedback_invitation_days', 7)),
        ]);

        $invitation->token = $plainToken;

        return $invitation;
    }
}
