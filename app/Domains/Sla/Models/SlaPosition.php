<?php

namespace App\Domains\Sla\Models;

use Carbon\CarbonImmutable;

final readonly class SlaPosition
{
    public function __construct(
        public SlaTargetType $targetType,
        public SlaClockState $state,
        public ?CarbonImmutable $dueAt,
        public int $targetMinutes,
        public int $elapsedMinutes,
        public int $remainingMinutes,
        public bool $warningFired,
        public ?CarbonImmutable $pausedAt,
    ) {}
}
