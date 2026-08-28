<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\IllegalDeliveryTransitionException;
use App\Domains\Ticketing\Exceptions\InternalNoteNotSendableException;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Services\Conversation\DeliveryStateMachine;
use Illuminate\Support\Facades\DB;

class TransitionMessageDelivery
{
    public function __construct(private readonly DeliveryStateMachine $stateMachine) {}

    public function handle(
        TicketMessage $message,
        MessageDeliveryState $to,
        ?string $reason = null,
        ?string $detail = null,
    ): TicketMessage {
        if ($message->is_internal) {
            throw new InternalNoteNotSendableException('Internal notes cannot be delivered');
        }

        if ($message->direction !== MessageDirection::Outbound) {
            throw new IllegalDeliveryTransitionException('Only outbound messages can be delivered');
        }

        $from = $message->delivery_state;
        if (! $this->stateMachine->canTransition($from, $to, $message->channel)) {
            throw new IllegalDeliveryTransitionException('This delivery state transition is not allowed');
        }

        return DB::transaction(function () use ($message, $to, $reason, $detail, $from) {
            $message->delivery_state = $to;

            $timestamp = match ($to) {
                MessageDeliveryState::Sent => 'sent_at',
                MessageDeliveryState::Delivered => 'delivered_at',
                MessageDeliveryState::Read => 'read_at',
                MessageDeliveryState::Failed => 'failed_at',
                MessageDeliveryState::Queued => 'queued_at',
            };

            $message->setAttribute($timestamp, now());

            if ($to === MessageDeliveryState::Failed) {
                $message->failure_reason = $reason;
                $message->failure_detail = $detail;
            } else {
                $message->failure_detail = null;
            }

            $message->save();

            $message->deliveryEvents()->create([
                'from_state' => $from,
                'to_state' => $to,
                'reason' => $reason,
                'detail' => $detail,
                'occurred_at' => now(),
            ]);

            return $message;
        });
    }
}
