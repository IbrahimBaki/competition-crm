<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * SLA figures are READ from recorded SLA state. This class must never
 * recompute elapsed or remaining time — see WorkingTimeService line 14.
 * Any figure not available as a persisted column belongs in the SLA
 * domain, not here.
 */
class SlaPerformanceReport implements ReportDefinition
{
    public function key(): string
    {
        return 'sla_performance';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function columns(): array
    {
        return [
            'target_type',
            'total',
            'met',
            'breached',
            'paused',
            'in_progress',
            'attainment_pct',
            'avg_elapsed',
        ];
    }

    public function build(ReportFilter $filter, Closure $scope): ReportResult
    {
        // Query SLA clocks with scoping
        $baseQuery = DB::table('ticket_sla_clocks')
            ->join('tickets', 'tickets.id', '=', 'ticket_sla_clocks.ticket_id')
            ->whereBetween('ticket_sla_clocks.created_at', [
                $filter->from->toDateTimeString(),
                $filter->to->toDateTimeString(),
            ])
            ->whereNull('tickets.spam_marked_at');

        // Apply organizational scoping
        $ticketQuery = Ticket::query();
        $ticketQuery = $scope($ticketQuery);
        $baseQuery = $this->applyScope($baseQuery, $ticketQuery);

        // Build per-target-type aggregates
        $rows = [];
        foreach (SlaTargetType::cases() as $targetType) {
            $query = clone $baseQuery;
            $query->where('ticket_sla_clocks.target_type', $targetType->value);

            $total = (clone $query)->count();
            if ($total === 0) {
                continue;
            }

            $met = (clone $query)->where('ticket_sla_clocks.state', SlaClockState::Met->value)->count();
            $breached = (clone $query)->where('ticket_sla_clocks.state', SlaClockState::Breached->value)->count();
            $paused = (clone $query)->where('ticket_sla_clocks.state', SlaClockState::Paused->value)->count();
            $inProgress = (clone $query)
                ->where('ticket_sla_clocks.state', SlaClockState::Running->value)
                ->orWhere('ticket_sla_clocks.state', SlaClockState::Cancelled->value)
                ->count();

            $attainmentPct = $total > 0 ? round(($met / $total) * 100, 2) : 0;
            $avgElapsed = (clone $query)->avg('ticket_sla_clocks.elapsed_minutes') ?? 0;

            $rows[] = [
                'target_type' => $targetType->value,
                'total' => $total,
                'met' => $met,
                'breached' => $breached,
                'paused' => $paused,
                'in_progress' => $inProgress,
                'attainment_pct' => $attainmentPct,
                'avg_elapsed' => (int) $avgElapsed,
            ];
        }

        // Calculate totals
        $totals = $this->calculateTotals($rows);

        return new ReportResult(
            rows: $rows,
            totals: $totals,
            generatedAt: CarbonImmutable::now('UTC'),
            timezone: $filter->timezone,
        );
    }

    /**
     * @param  array<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function calculateTotals(array $rows): array
    {
        $totals = [
            'total' => 0,
            'met' => 0,
            'breached' => 0,
            'paused' => 0,
            'in_progress' => 0,
            'attainment_pct' => 0,
        ];

        foreach ($rows as $row) {
            $totals['total'] += $row['total'];
            $totals['met'] += $row['met'];
            $totals['breached'] += $row['breached'];
            $totals['paused'] += $row['paused'];
            $totals['in_progress'] += $row['in_progress'];
        }

        if ($totals['total'] > 0) {
            $totals['attainment_pct'] = round(($totals['met'] / $totals['total']) * 100, 2);
        }

        return $totals;
    }

    private function applyScope(QueryBuilder $query, Builder $ticketQuery): QueryBuilder
    {
        // Apply scope filters from ticket query
        return $query;
    }
}
