<?php

namespace App\Domains\Channels\Messaging\Jobs;

use App\Domains\Channels\Messaging\Services\Delivery\ApplyProviderReceipt;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyProviderDeliveryReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $channel,
        public readonly string $providerMessageId,
        public readonly MessageDeliveryState $status,
        public readonly ?string $failureReason = null,
        public readonly ?string $failureDetail = null,
    ) {}

    public function handle(ApplyProviderReceipt $service): void
    {
        $service->handle(
            channel: $this->channel,
            providerMessageId: $this->providerMessageId,
            status: $this->status,
            failureReason: $this->failureReason,
            failureDetail: $this->failureDetail,
        );
    }
}
