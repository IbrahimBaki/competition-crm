<?php

namespace App\Domains\Channels\Messaging\Jobs;

use App\Domains\Channels\Messaging\Services\Inbound\ProcessProviderInboundMessage;
use App\Domains\Ticketing\Models\MessageChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProviderInboundMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly MessageChannel $channel,
        public readonly array $payload,
        public readonly string $receivedAt,
    ) {}

    public function handle(ProcessProviderInboundMessage $service): void
    {
        $service->handle($this->channel, $this->payload, $this->receivedAt);
    }
}
