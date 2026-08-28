<?php

namespace App\Domains\Channels\Chat\Services\Availability;

use App\Domains\Channels\Chat\Models\ChatAvailabilityOutcome;

final class ChatAvailability
{
    public function __construct(
        public readonly ChatAvailabilityOutcome $outcome,
        public readonly ?int $queuePosition = null,
        public readonly ?int $estimatedWaitSeconds = null,
    ) {}
}
