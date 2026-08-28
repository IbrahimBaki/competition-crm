<?php

namespace App\Domains\Channels\Messaging\Services\Transport;

use App\Domains\Ticketing\Models\TicketMessage;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

final class NullProviderMessageTransport implements ProviderMessageTransport
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function send(TicketMessage $message): ProviderSendResult
    {
        $providerMessageId = Str::uuid()->toString();

        $this->logger->info('Provider message queued (null transport)', [
            'ticket_message_id' => $message->id,
            'channel' => $message->channel->value,
            'provider_message_id' => $providerMessageId,
        ]);

        return ProviderSendResult::success($providerMessageId);
    }
}
