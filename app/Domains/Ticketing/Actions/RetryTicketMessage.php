<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\MessageNotRetryableException;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use Illuminate\Support\Facades\DB;

class RetryTicketMessage
{
    public function __construct(
        private readonly TransitionMessageDelivery $transitionAction,
        private readonly RecordTicketEvent $eventRecorder,
    ) {}

    public function handle(TicketMessage $message): TicketMessage
    {
        if (! $message->isRetryable()) {
            throw new MessageNotRetryableException('This message cannot be retried');
        }

        return DB::transaction(function () use ($message) {
            $message->retry_count = ($message->retry_count ?? 0) + 1;
            $message->failed_at = null;
            $message->save();

            $message = $this->transitionAction->handle($message, MessageDeliveryState::Queued);

            $this->eventRecorder->handle(
                $message->ticket,
                TicketEventType::MessageRetried,
                null,
                [
                    'message_uuid' => $message->uuid,
                    'retry_count' => $message->retry_count,
                ]
            );

            return $message;
        });
    }
}
