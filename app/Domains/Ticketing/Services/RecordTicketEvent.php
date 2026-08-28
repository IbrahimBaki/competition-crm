<?php

namespace App\Domains\Ticketing\Services;

use App\Domains\Integrations\Services\Webhooks\DispatchWebhooks;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEvent;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordTicketEvent
{
    public function __construct(
        private readonly DispatchWebhooks $dispatchWebhooks,
    ) {}

    public function handle(Ticket $ticket, TicketEventType $type, ?User $actor, array $payload = []): TicketEvent
    {
        $event = $ticket->events()->create([
            'type' => $type,
            'actor_user_id' => $actor?->id,
            'payload' => $payload ?: null,
            'occurred_at' => now(),
        ]);

        DB::afterCommit(function () use ($event, $type) {
            $this->dispatchWebhooks->dispatch($type->value, $event->uuid, [
                'event_type' => $type->value,
                'ticket_id' => $event->ticket->uuid,
                'payload' => $event->payload,
            ]);
        });

        return $event;
    }
}
