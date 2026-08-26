<?php

namespace App\Domains\Customers\Services\Timeline;

readonly class TimelineEntry
{
    public function __construct(
        public string $id,
        public string $source,
        public string $type,
        public \DateTimeImmutable $occurredAt,
        public ?string $actorUuid,
        public array $payload,
    ) {}
}
