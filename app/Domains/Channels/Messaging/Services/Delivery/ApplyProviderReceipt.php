<?php

namespace App\Domains\Channels\Messaging\Services\Delivery;

use App\Domains\Ticketing\Actions\TransitionMessageDelivery;
use App\Domains\Ticketing\Exceptions\IllegalDeliveryTransitionException;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\TicketMessage;
use Psr\Log\LoggerInterface;

class ApplyProviderReceipt
{
    public function __construct(
        private readonly TransitionMessageDelivery $transitionDelivery,
        private readonly LoggerInterface $logger,
    ) {}

    public function handle(
        string $channel,
        string $providerMessageId,
        MessageDeliveryState $status,
        ?string $failureReason = null,
        ?string $failureDetail = null,
    ): void {
        $message = TicketMessage::query()
            ->where('channel', $channel)
            ->where('external_message_id', $providerMessageId)
            ->first();

        if (! $message) {
            $this->logger->warning('Provider receipt for unknown message', [
                'channel' => $channel,
                'provider_message_id' => $providerMessageId,
                'status' => $status->value,
            ]);

            return;
        }

        try {
            $this->transitionDelivery->handle(
                message: $message,
                toState: $status,
                failureReason: $failureReason,
                failureDetail: $failureDetail,
            );
        } catch (IllegalDeliveryTransitionException $e) {
            $this->logger->warning('Out-of-order or invalid delivery receipt', [
                'ticket_message_id' => $message->id,
                'current_state' => $message->delivery_state?->value,
                'receipt_state' => $status->value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
