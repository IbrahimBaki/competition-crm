<?php

namespace App\Domains\Channels\Email\Services\Delivery;

use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Ticketing\Actions\TransitionMessageDelivery;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\TicketMessage;

class ApplyBounce
{
    public function __construct(private readonly TransitionMessageDelivery $transitionDelivery) {}

    public function handle(InboundEmailMessage $record): void
    {
        $message = $this->findBounced($record);

        if (! $message) {
            return;
        }

        $reason = $this->extractReason($record);

        $this->transitionDelivery->handle(
            $message,
            MessageDeliveryState::Failed,
            $reason,
            $record->last_error,
        );
    }

    private function findBounced(InboundEmailMessage $record): ?TicketMessage
    {
        if ($record->ticket_message_id) {
            return TicketMessage::find($record->ticket_message_id);
        }

        return null;
    }

    private function extractReason(InboundEmailMessage $record): string
    {
        if (str_contains($record->last_error ?? '', '5.')) {
            return 'Hard bounce (permanent failure)';
        }

        if (str_contains($record->last_error ?? '', '4.')) {
            return 'Soft bounce (temporary failure)';
        }

        return 'Bounce received from email provider';
    }
}
