<?php

namespace App\Support\Retention;

use Carbon\CarbonImmutable;

final class RetentionPolicy
{
    public function __construct(
        public readonly string $dataClass,
        public readonly ?int $days,
        public readonly CarbonImmutable $cutoff,
    ) {}

    public static function fromConfig(string $dataClass, array $config): self
    {
        $days = $config['days'] ?? null;
        $cutoff = $days !== null
            ? now()->subDays($days)->toImmutable()
            : CarbonImmutable::now();

        return new self($dataClass, $days, $cutoff);
    }
}
