<?php

namespace App\Domains\Channels\Messaging\Services\Inbound;

use App\Domains\Channels\Messaging\Actions\RecordMessagingConsent;
use App\Domains\Channels\Messaging\Models\ProviderInboundMessage;
use App\Domains\Channels\Messaging\Services\Subject\DeriveSubjectFromBody;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use Psr\Log\LoggerInterface;

final class ProcessProviderInboundMessage
{
    public function __construct(
        private readonly CustomerIdentityResolver $identityResolver,
        private readonly RecordMessagingConsent $consentRecorder,
        private readonly DeriveSubjectFromBody $subjectDeriver,
        private readonly CreateTicket $ticketCreator,
        private readonly LoggerInterface $logger,
    ) {}

    public function handle(MessageChannel $channel, array $payload, string $receivedAt): void
    {
        $inboundMessage = $this->storeRawMessage($channel, $payload, $receivedAt);

        if (! $inboundMessage) {
            return;
        }

        $phoneNumber = $payload['from'] ?? $payload['from_number'] ?? null;
        if (! $phoneNumber) {
            $this->logger->warning('Provider inbound message missing sender', [
                'channel' => $channel->value,
                'provider_message_id' => $payload['id'] ?? null,
            ]);

            return;
        }

        $contact = $this->identityResolver->resolveByPhone($phoneNumber);

        if (! $contact) {
            $this->logger->info('Provider inbound from unknown contact', [
                'channel' => $channel->value,
                'phone' => $phoneNumber,
            ]);

            return;
        }

        $body = $payload['body'] ?? $payload['text'] ?? '';

        if ($channel === MessageChannel::Sms && $this->isSmsStopKeyword($body)) {
            $this->consentRecorder->optOutSms($contact, 'customer_request');
            $inboundMessage->update(['state' => 'processed']);

            return;
        }

        if ($channel === MessageChannel::Whatsapp) {
            $this->consentRecorder->optInWhatsapp($contact, 'inbound_message');
        }

        $ticket = $this->findOrCreateTicket($contact, $channel, $body);
        $inboundMessage->update(['ticket_id' => $ticket->id, 'state' => 'processed']);
    }

    private function storeRawMessage(MessageChannel $channel, array $payload, string $receivedAt): ?ProviderInboundMessage
    {
        $providerMessageId = $payload['id'] ?? $payload['message_id'] ?? null;

        if (! $providerMessageId) {
            return null;
        }

        try {
            return ProviderInboundMessage::create([
                'channel' => $channel,
                'provider_message_id' => $providerMessageId,
                'from_identifier' => $payload['from'] ?? $payload['from_number'] ?? 'unknown',
                'raw_payload' => $payload,
                'state' => 'received',
                'received_at' => $receivedAt,
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to store inbound message', [
                'channel' => $channel->value,
                'provider_message_id' => $providerMessageId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function findOrCreateTicket(CustomerContact $contact, MessageChannel $channel, string $body): Ticket
    {
        $ticket = Ticket::query()
            ->where('customer_id', $contact->customer_id)
            ->where('status', '!=', 'closed')
            ->orderByDesc('created_at')
            ->first();

        if ($ticket) {
            return $ticket;
        }

        $subject = $this->subjectDeriver->derive(
            $body,
            config('channels.derived_subject_length', 80),
            __('channels.inbound_message_placeholder', [], 'en')
        );

        return $this->ticketCreator->handle(
            customer: $contact->customer,
            subject: $subject,
            department: $contact->customer->primaryContact?->id ? null : $contact->customer->department ?? null,
        );
    }

    private function isSmsStopKeyword(string $body): bool
    {
        $normalized = strtoupper(trim($body));

        return in_array($normalized, ['STOP', 'الغاء'], true);
    }
}
