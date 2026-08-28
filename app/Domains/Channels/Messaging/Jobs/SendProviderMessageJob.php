<?php

namespace App\Domains\Channels\Messaging\Jobs;

use App\Domains\Channels\Messaging\Services\Transport\ProviderMessageTransport;
use App\Domains\Ticketing\Actions\TransitionMessageDelivery;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Log\LoggerInterface;
use Throwable;

class SendProviderMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [30, 120, 600];

    public function __construct(
        public readonly TicketMessage $message,
    ) {}

    public function handle(
        ProviderMessageTransport $transport,
        TransitionMessageDelivery $transitioner,
        LoggerInterface $logger,
    ): void {
        try {
            $result = $transport->send($this->message);

            if ($result->success) {
                $this->message->update(['external_message_id' => $result->providerMessageId]);

                $transitioner->handle(
                    message: $this->message,
                    toState: MessageDeliveryState::Sent,
                );

                $logger->info('Provider message sent successfully', [
                    'ticket_message_id' => $this->message->id,
                    'provider_message_id' => $result->providerMessageId,
                ]);
            } else {
                throw new \RuntimeException($result->failureDetail ?? $result->failureReason ?? 'Unknown error');
            }
        } catch (Throwable $e) {
            $logger->warning('Provider message send attempt failed', [
                'ticket_message_id' => $this->message->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $transitioner = app(TransitionMessageDelivery::class);
        $logger = app(LoggerInterface::class);

        $reason = class_basename($exception);
        $detail = $exception->getMessage();

        $transitioner->handle(
            message: $this->message,
            toState: MessageDeliveryState::Failed,
            failureReason: 'provider_send_failed',
            failureDetail: "{$reason}: {$detail}",
        );

        $logger->error('Provider message send exhausted', [
            'ticket_message_id' => $this->message->id,
            'failure_reason' => $reason,
            'failure_detail' => $detail,
        ]);
    }
}
