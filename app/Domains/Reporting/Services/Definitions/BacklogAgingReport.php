<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\DB;

class BacklogAgingReport implements ReportDefinition
{
    public function key(): string
    {
        return 'backlog_aging';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function columns(): array
    {
        return [
            'age_band',
            'department_uuid',
            'department_name',
            'priority',
            'count',
        ];
    }

    public function build(ReportFilter $filter, Closure $scope): ReportResult
    {
        // Get open ticket status IDs
        $openStatusIds = TicketStatusDefinition::where('lifecycle_type', 'open')
            ->pluck('id')
            ->all();

        if (empty($openStatusIds)) {
            return new ReportResult(
                rows: [],
                totals: ['total_open' => 0],
                generatedAt: CarbonImmutable::now('UTC'),
                timezone: $filter->timezone,
            );
        }

        // Query open tickets with age calculation
        $now = CarbonImmutable::now('UTC');
        $rows = [];

        foreach ($this->getAgeBands() as $ageBand => $dayRange) {
            [$minDays, $maxDays] = $dayRange;

            $minAge = $now->subDays($maxDays);
            $maxAge = $now->subDays($minDays);

            $query = DB::table('tickets')
                ->join('departments', 'departments.id', '=', 'tickets.department_id')
                ->select(
                    DB::raw("'{$ageBand}' as age_band"),
                    'departments.id as uuid',
                    'departments.name',
                    'tickets.priority',
                    DB::raw('COUNT(*) as count')
                )
                ->whereIn('tickets.ticket_status_id', $openStatusIds)
                ->whereNull('tickets.spam_marked_at')
                ->whereBetween('tickets.created_at', [$minAge, $maxAge])
                ->groupBy('departments.id', 'departments.name', 'tickets.priority');

            $results = $query->get();
            foreach ($results as $row) {
                $rows[] = [
                    'age_band' => $row->age_band,
                    'department_uuid' => $row->uuid,
                    'department_name' => json_decode($row->name, true),
                    'priority' => $row->priority,
                    'count' => (int) $row->count,
                ];
            }
        }

        $totals = ['total_open' => array_sum(array_column($rows, 'count'))];

        return new ReportResult(
            rows: $rows,
            totals: $totals,
            generatedAt: CarbonImmutable::now('UTC'),
            timezone: $filter->timezone,
        );
    }

    /**
     * Get age band definitions [minDays, maxDays).
     *
     * @return array<string, array<int>>
     */
    private function getAgeBands(): array
    {
        return [
            '0-1d' => [0, 1],
            '1-3d' => [1, 3],
            '3-7d' => [3, 7],
            '7-30d' => [7, 30],
            '30d+' => [30, PHP_INT_MAX],
        ];
    }
}
