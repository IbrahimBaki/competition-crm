<?php

namespace App\Domains\Ticketing\Services;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEvent;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Models\User;

class RecordTicketEvent
{
    public function handle(Ticket $ticket, TicketEventType $type, ?User $actor, array $payload = []): TicketEvent
    {
        return $ticket->events()->create([
            'type' => $type,
            'actor_user_id' => $actor?->id,
            'payload' => $payload ?: null,
            'occurred_at' => now(),
        ]);
    }
}
