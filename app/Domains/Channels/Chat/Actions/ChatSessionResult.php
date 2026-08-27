<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Services\Availability\ChatAvailability;

final class ChatSessionResult
{
    public function __construct(
        public readonly ChatSession $session,
        public readonly string $visitorToken,
        public readonly ChatAvailability $availability,
    ) {}
}
