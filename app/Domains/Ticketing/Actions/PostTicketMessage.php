<?php

namespace App\Domains\Ticketing\Actions;

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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PostTicketMessage
{
    public function __construct(
        private readonly RecordTicketEvent $eventRecorder,
        private readonly SlaClockHooks $slaHooks,
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
    ): TicketMessage {
        if ($ticket->isMerged() || $ticket->isSpam()) {
            throw new TicketConversationReadOnlyException('Conversation on this ticket is read-only');
        }

        return DB::transaction(function () use ($ticket, $actor, $channel, $body, $isInternal, $bodyFormat, $attachmentUuids) {
            if ($isInternal) {
                $channel = MessageChannel::Internal;
                $deliveryState = null;
            } else {
                $deliveryState = MessageDeliveryState::Queued;
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
            ]);

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
}
