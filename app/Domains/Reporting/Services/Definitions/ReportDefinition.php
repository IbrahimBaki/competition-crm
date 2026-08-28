<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use Closure;

interface ReportDefinition
{
    /**
     * Unique key for this report.
     * Examples: "ticket_volume", "sla_performance", "agent_performance"
     */
    public function key(): string;

    /**
     * Permission resource prefix for viewing this report.
     * Examples: "reports.view"
     * The scope (own/department/branch/any) is added by the authorization layer.
     */
    public function permission(): string;

    /**
     * Ordered column keys for tabular output and export headers.
     *
     * @return list<string>
     */
    public function columns(): array;

    /**
     * Build the report with the given filter.
     * The scope closure applies organizational scoping to queries.
     *
     * @param  Closure(mixed): mixed  $scope
     */
    public function build(ReportFilter $filter, Closure $scope): ReportResult;
}
