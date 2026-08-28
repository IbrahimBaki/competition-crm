<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Filters;

use Carbon\CarbonImmutable;

final readonly class ReportFilter
{
    public function __construct(
        public CarbonImmutable $from,        // inclusive, UTC instant
        public CarbonImmutable $to,          // exclusive, UTC instant
        public string $timezone,             // IANA zone used for bucketing/presentation
        public ?int $branchId = null,
        public ?int $departmentId = null,
        public ?int $teamId = null,
        public ?int $agentId = null,
        public ?int $categoryId = null,
        public ?string $priority = null,     // TicketPriority value
        public ?string $channel = null,      // MessageChannel value
        public ?int $tagId = null,
    ) {}
}
