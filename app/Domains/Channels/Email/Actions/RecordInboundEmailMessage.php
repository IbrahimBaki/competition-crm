<?php

namespace App\Domains\Channels\Email\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use Illuminate\Support\Facades\DB;

class RecordInboundEmailMessage
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly SlaClockHooks $slaHooks,
    ) {}

    public function handle(Ticket $ticket, TicketMessage $message): void
    {
        DB::transaction(function () use ($ticket, $message) {
            $this->recordEvent->handle(
                $ticket,
                TicketEventType::MessagePosted,
                null,
                [
                    'message_id' => $message->uuid,
                    'channel' => $message->channel->value,
                    'is_internal' => $message->is_internal,
                ],
            );

            $this->slaHooks->onCustomerReply($ticket, $message);
        });
    }
}
