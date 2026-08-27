<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ai\Exceptions\AiSuggestionNotApprovedException;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Channels\Messaging\Exceptions\WhatsappFreeFormWindowClosedException;
use App\Domains\Channels\Messaging\Models\ProviderMessageTemplate;
use App\Domains\Ticketing\Exceptions\TicketConversationReadOnlyException;
use App\Domains\Ticketing\Models\MessageAuthorType;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\Exceptions\AttachmentInfectedException;
use App\Support\Attachments\Exceptions\AttachmentPendingScanException;
use App\Support\Attachments\ScanState;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PostTicketMessage
{
    public function __construct(
        private readonly RecordTicketEvent $eventRecorder,
        private readonly SlaClockHooks $slaHooks,
        private readonly MessagingConsentGuard $consentGuard,
        private readonly WhatsappWindowPolicy $windowPolicy,
        private readonly ProviderTemplateRenderer $templateRenderer,
        private readonly LocaleResolver $localeResolver,
    ) {}

    /**
     * @param  list<string>  $attachmentUuids
     */
    public function handle(
        Ticket $ticket,
        User $actor,
        MessageChannel $channel,
        string $body,
        bool $isInternal = false,
        string $bodyFormat = 'text',
        array $attachmentUuids = [],
        ?string $templateKey = null,
        array $templateVariables = [],
        ?int $aiSuggestionId = null,
    ): TicketMessage {
        if ($ticket->isMerged() || $ticket->isSpam()) {
            throw new TicketConversationReadOnlyException('Conversation on this ticket is read-only');
        }

        // Validate AI suggestion if provided (criterion 1 enforcement)
        $suggestion = null;
        if ($aiSuggestionId !== null) {
            $suggestion = AiSuggestion::query()->find($aiSuggestionId);

            if ($suggestion === null || $suggestion->ticket_id !== $ticket->id || $suggestion->state !== AiSuggestionState::Accepted || $suggestion->resolved_by_user_id === null) {
                throw new AiSuggestionNotApprovedException('AI-generated content must be explicitly approved before sending to customers');
            }
        }

        return DB::transaction(function () use ($ticket, $actor, $channel, $body, $isInternal, $bodyFormat, $attachmentUuids, $templateKey, $templateVariables, $aiSuggestionId, $suggestion) {
            if ($isInternal) {
                $channel = MessageChannel::Internal;
                $deliveryState = null;
            } else {
                $deliveryState = MessageDeliveryState::Queued;

                // Guard chain for messaging channels (before persistence)
                $body = $this->applyOutboundGuards($ticket, $channel, $body, $templateKey, $templateVariables);
            }

            $message = $ticket->messages()->create([
                'direction' => MessageDirection::Outbound,
                'author_type' => MessageAuthorType::Agent,
                'author_user_id' => $actor->id,
                'channel' => $channel,
                'is_internal' => $isInternal,
                'body' => $body,
                'body_format' => $bodyFormat,
                'delivery_state' => $deliveryState,
                'queued_at' => $deliveryState === MessageDeliveryState::Queued ? now() : null,
                'ai_suggestion_id' => $aiSuggestionId,
            ]);

            // Transition suggestion to Sent after message is created
            if ($suggestion !== null) {
                $suggestion->update(['state' => AiSuggestionState::Sent]);
            }

            if ($deliveryState === MessageDeliveryState::Queued) {
                $message->deliveryEvents()->create([
                    'from_state' => null,
                    'to_state' => MessageDeliveryState::Queued,
                    'occurred_at' => now(),
                ]);
            }

            foreach ($attachmentUuids as $uuid) {
                $attachment = Attachment::query()->where('uuid', $uuid)->firstOrFail();

                if ($attachment->uploaded_by !== $actor->id) {
                    throw new AuthorizationException('Attachment not uploaded by current user');
                }

                if ($attachment->scan_state === ScanState::Pending) {
                    throw new AttachmentPendingScanException;
                }

                if ($attachment->scan_state === ScanState::Infected) {
                    throw new AttachmentInfectedException;
                }

                if ($attachment->attachable_type !== null) {
                    throw new \InvalidArgumentException('Attachment is already linked elsewhere');
                }

                $attachment->attachable_type = TicketMessage::class;
                $attachment->attachable_id = $message->id;
                $attachment->save();
            }

            $eventType = $isInternal ? TicketEventType::InternalNoteAdded : TicketEventType::MessagePosted;
            $this->eventRecorder->handle(
                $ticket,
                $eventType,
                null,
                [
                    'message_uuid' => $message->uuid,
                    'is_internal' => $isInternal,
                ]
            );

            if (! $isInternal && $message->direction === MessageDirection::Outbound) {
                $this->slaHooks->firstAgentReplySent($ticket, CarbonImmutable::now('UTC'));
            }

            return $message;
        });
    }

    private function applyOutboundGuards(
        Ticket $ticket,
        MessageChannel $channel,
        string $body,
        ?string $templateKey,
        array $templateVariables,
    ): string {
        // For non-messaging channels, no guards apply
        if (! in_array($channel, [MessageChannel::Whatsapp, MessageChannel::Sms], true)) {
            return $body;
        }

        // Get the customer's contact for this channel
        $contact = $ticket->customer->contacts()
            ->where('type', $channel === MessageChannel::Whatsapp ? 'whatsapp' : 'phone')
            ->first();

        if (! $contact) {
            $contact = $ticket->customer->contacts()
                ->whereIn('type', ['whatsapp', 'phone'])
                ->first();
        }

        if (! $contact) {
            throw new \InvalidArgumentException('Customer has no contact information for '.$channel->value);
        }

        // Check consent
        $this->consentGuard->assertMayReceive($contact, $channel);

        // WhatsApp: check window or use template
        if ($channel === MessageChannel::Whatsapp) {
            if ($templateKey) {
                // Use template path
                $template = ProviderMessageTemplate::query()
                    ->where('channel', MessageChannel::Whatsapp)
                    ->where('key', $templateKey)
                    ->firstOrFail();

                $locale = $this->localeResolver->resolve($contact->customer->locale ?? 'en');

                return $this->templateRenderer->render($template, $templateVariables, $locale);
            } else {
                // Free-form path - check window
                if (! $this->windowPolicy->isOpen($ticket, now())) {
                    $expiresAt = $this->windowPolicy->expiresAt($ticket);

                    throw new WhatsappFreeFormWindowClosedException($expiresAt ?? now());
                }
            }
        }

        return $body;
    }
}
