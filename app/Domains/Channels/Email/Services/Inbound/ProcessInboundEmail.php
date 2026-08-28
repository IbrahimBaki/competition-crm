<?php

namespace App\Domains\Channels\Email\Services\Inbound;

use App\Domains\Channels\Email\Actions\RecordInboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundClassification;
use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use App\Domains\Channels\Email\Services\Classification\InboundClassifier;
use App\Domains\Channels\Email\Services\Correlation\EmailCorrelator;
use App\Domains\Channels\Email\Services\Delivery\ApplyBounce;
use App\Domains\Channels\Email\Services\Parsing\EmailParser;
use App\Domains\Channels\Email\Services\Parsing\QuotedTextStripper;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Models\MessageAuthorType;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Models\TicketPriority;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessInboundEmail
{
    public function __construct(
        private readonly EmailParser $parser,
        private readonly InboundClassifier $classifier,
        private readonly EmailCorrelator $correlator,
        private readonly QuotedTextStripper $stripper,
        private readonly ApplyBounce $applyBounce,
        private readonly CustomerIdentityResolver $identityResolver,
        private readonly CreateTicket $createTicket,
        private readonly RecordInboundEmailMessage $recordInbound,
    ) {}

    public function handle(InboundEmailMessage $record): void
    {
        DB::transaction(function () use ($record) {
            $rawMime = Storage::disk('local')->get($record->raw_path);
            $email = $this->parser->parse($rawMime);

            $classification = $this->classifier->classify($email);

            if ($classification === InboundClassification::Bounce) {
                $record->update(['classification' => $classification, 'state' => InboundState::Processed]);
                $this->applyBounce->handle($record);

                return;
            }

            if (in_array($classification, [InboundClassification::AutoReply, InboundClassification::Loop])) {
                $record->update(['classification' => $classification, 'state' => InboundState::Suppressed]);

                return;
            }

            if ($email->messageId) {
                $existing = TicketMessage::where('channel', MessageChannel::Email->value)
                    ->where('external_message_id', $email->messageId)
                    ->first();

                if ($existing) {
                    $record->update([
                        'classification' => InboundClassification::Reply,
                        'ticket_id' => $existing->ticket_id,
                        'ticket_message_id' => $existing->id,
                        'state' => InboundState::Processed,
                    ]);

                    return;
                }
            }

            $ticket = $this->correlator->correlate($email);

            if (! $ticket) {
                $identity = $this->identityResolver->resolve([
                    ['type' => 'email', 'value' => $email->fromAddress],
                ]);

                $customer = $identity->customer;
                $department = Department::first();
                $priority = TicketPriority::cases()[0];

                $ticket = $this->createTicket->handle(
                    customer: $customer,
                    department: $department,
                    priority: $priority,
                    subject: $email->subject ?? '(no subject)',
                    body: $email->textBody,
                );

                $classification = InboundClassification::New;
            } else {
                $classification = InboundClassification::Reply;
            }

            $strippedBody = $this->stripper->strip($email->textBody);

            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'direction' => MessageDirection::Inbound->value,
                'author_type' => MessageAuthorType::Customer->value,
                'channel' => MessageChannel::Email->value,
                'body' => $strippedBody,
                'body_format' => 'text',
                'external_message_id' => $email->messageId,
                'in_reply_to' => $email->inReplyTo,
                'reference_ids' => json_encode($email->referenceIds),
                'raw_source_path' => $record->raw_path,
                'inbound_classification' => $classification->value,
            ]);

            $this->recordInbound->handle($ticket, $message);

            $record->update([
                'ticket_id' => $ticket->id,
                'ticket_message_id' => $message->id,
                'classification' => $classification,
                'state' => InboundState::Processed,
                'processed_at' => now(),
            ]);
        });
    }
}
