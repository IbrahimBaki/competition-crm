<?php

namespace App\Domains\Channels\Email\Jobs;

use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use App\Domains\Channels\Email\Services\Inbound\ProcessInboundEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInboundEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public array $backoff = [60, 300, 900, 3600, 7200];

    public function __construct(private readonly int $recordId) {}

    public function handle(ProcessInboundEmail $processor): void
    {
        $record = InboundEmailMessage::find($this->recordId);

        if (! $record || $record->state !== InboundState::Received) {
            return;
        }

        try {
            $processor->handle($record);
        } catch (\Throwable $e) {
            $record->increment('attempts');
            $record->update([
                'last_error' => $e->getMessage(),
                'state' => InboundState::Failed,
            ]);

            throw $e;
        }
    }
}
