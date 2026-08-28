<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use Carbon\CarbonImmutable;
use Closure;

class ManagementDashboardReport implements ReportDefinition
{
    public function __construct(
        private TicketVolumeReport $volumeReport,
        private SlaPerformanceReport $slaReport,
        private AgentPerformanceReport $agentReport,
        private SatisfactionReport $satisfactionReport,
        private BacklogAgingReport $backlogReport,
    ) {}

    public function key(): string
    {
        return 'management_dashboard';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function columns(): array
    {
        return [
            'ticket_volume_totals',
            'sla_performance_totals',
            'agent_performance_totals',
            'satisfaction_totals',
            'backlog_totals',
        ];
    }

    public function build(ReportFilter $filter, Closure $scope): ReportResult
    {
        // Build all sub-reports
        $volume = $this->volumeReport->build($filter, $scope);
        $sla = $this->slaReport->build($filter, $scope);
        $agent = $this->agentReport->build($filter, $scope);
        $satisfaction = $this->satisfactionReport->build($filter, $scope);
        $backlog = $this->backlogReport->build($filter, $scope);

        // Combine totals
        $totals = [
            'ticket_volume' => $volume->totals,
            'sla_performance' => $sla->totals,
            'agent_performance' => $agent->totals,
            'satisfaction' => $satisfaction->totals,
            'backlog' => $backlog->totals,
        ];

        return new ReportResult(
            rows: [$totals],
            totals: $totals,
            generatedAt: CarbonImmutable::now('UTC'),
            timezone: $filter->timezone,
        );
    }
}
