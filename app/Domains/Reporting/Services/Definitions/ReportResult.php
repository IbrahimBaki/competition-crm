<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use Carbon\CarbonImmutable;

final readonly class ReportResult
{
    /**
     * @param  array<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $totals
     */
    public function __construct(
        public array $rows,
        public array $totals,
        public CarbonImmutable $generatedAt,
        public string $timezone,
    ) {}
}
