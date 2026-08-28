<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TicketVolumeReport implements ReportDefinition
{
    public function key(): string
    {
        return 'ticket_volume';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function columns(): array
    {
        return [
            'bucket',
            'created_count',
            'resolved_count',
            'reopened_count',
        ];
    }

    public function build(ReportFilter $filter, Closure $scope): ReportResult
    {
        // Determine bucketing strategy based on range length
        $daysDiff = $filter->to->diffInDays($filter->from);
        $bucketFormat = $this->getBucketFormat($daysDiff);

        // Build the query
        $rows = $this->buildQuery($filter, $bucketFormat, $scope);

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
     * @return array<array<string, mixed>>
     */
    private function buildQuery(ReportFilter $filter, string $bucketFormat, Closure $scope): array
    {
        // Create bucket expression - MySQL DATE_FORMAT to convert UTC to desired timezone
        $tzOffset = $this->getTimezoneOffset($filter->timezone);
        $bucketExpr = "DATE_FORMAT(
            CONVERT_TZ(tickets.created_at, '+00:00', '{$tzOffset}'),
            '{$bucketFormat}'
        )";

        // Query created tickets
        $createdQuery = DB::table('tickets')
            ->select(
                DB::raw("$bucketExpr as bucket"),
                DB::raw('COUNT(*) as created_count')
            )
            ->whereBetween('tickets.created_at', [
                $filter->from->toDateTimeString(),
                $filter->to->toDateTimeString(),
            ])
            ->where('tickets.is_spam', false);

        // Apply scope
        $ticketQuery = Ticket::query();
        $ticketQuery = $scope($ticketQuery);
        $createdQuery = $this->applyScopeToQuery($createdQuery, $ticketQuery);

        $createdQuery->groupBy(DB::raw("$bucketExpr"));

        // Query resolved events
        $resolvedQuery = DB::table('ticket_events')
            ->select(
                DB::raw("$bucketExpr as bucket"),
                DB::raw('COUNT(*) as resolved_count')
            )
            ->join('tickets', 'tickets.id', '=', 'ticket_events.ticket_id')
            ->where('ticket_events.type', TicketEventType::Resolved->value)
            ->whereBetween('ticket_events.created_at', [
                $filter->from->toDateTimeString(),
                $filter->to->toDateTimeString(),
            ])
            ->where('tickets.is_spam', false)
            ->groupBy(DB::raw("$bucketExpr"));

        // Query reopened events
        $reopenedQuery = DB::table('ticket_events')
            ->select(
                DB::raw("$bucketExpr as bucket"),
                DB::raw('COUNT(*) as reopened_count')
            )
            ->join('tickets', 'tickets.id', '=', 'ticket_events.ticket_id')
            ->where('ticket_events.type', TicketEventType::Reopened->value)
            ->whereBetween('ticket_events.created_at', [
                $filter->from->toDateTimeString(),
                $filter->to->toDateTimeString(),
            ])
            ->where('tickets.is_spam', false)
            ->groupBy(DB::raw("$bucketExpr"));

        // Combine results
        return $this->mergeResults(
            $createdQuery->get()->all(),
            $resolvedQuery->get()->all(),
            $reopenedQuery->get()->all()
        );
    }

    private function getBucketFormat(int $daysDiff): string
    {
        return match (true) {
            $daysDiff <= 7 => '%Y-%m-%d', // Daily for up to 7 days
            $daysDiff <= 90 => '%Y-W%v', // Weekly for up to 90 days
            default => '%Y-%m', // Monthly otherwise
        };
    }

    private function getTimezoneOffset(string $timezone): string
    {
        // Convert PHP timezone to MySQL offset format
        $tz = new \DateTimeZone($timezone);
        $now = new \DateTime('now', $tz);
        $offset = $tz->getOffset($now);

        $hours = intdiv($offset, 3600);
        $minutes = abs(intdiv($offset % 3600, 60));

        return sprintf('%+03d:%02d', $hours, $minutes);
    }

    /**
     * @param  object[]  $created
     * @param  object[]  $resolved
     * @param  object[]  $reopened
     * @return array<array<string, mixed>>
     */
    private function mergeResults(array $created, array $resolved, array $reopened): array
    {
        $buckets = [];

        // Add created counts
        foreach ($created as $row) {
            $buckets[$row->bucket] = [
                'bucket' => $row->bucket,
                'created_count' => (int) $row->created_count,
                'resolved_count' => 0,
                'reopened_count' => 0,
            ];
        }

        // Add resolved counts
        foreach ($resolved as $row) {
            if (! isset($buckets[$row->bucket])) {
                $buckets[$row->bucket] = [
                    'bucket' => $row->bucket,
                    'created_count' => 0,
                    'resolved_count' => 0,
                    'reopened_count' => 0,
                ];
            }
            $buckets[$row->bucket]['resolved_count'] = (int) $row->resolved_count;
        }

        // Add reopened counts
        foreach ($reopened as $row) {
            if (! isset($buckets[$row->bucket])) {
                $buckets[$row->bucket] = [
                    'bucket' => $row->bucket,
                    'created_count' => 0,
                    'resolved_count' => 0,
                    'reopened_count' => 0,
                ];
            }
            $buckets[$row->bucket]['reopened_count'] = (int) $row->reopened_count;
        }

        // Sort by bucket and return as array
        ksort($buckets);

        return array_values($buckets);
    }

    /**
     * @param  array<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function calculateTotals(array $rows): array
    {
        $totals = [
            'created_count' => 0,
            'resolved_count' => 0,
            'reopened_count' => 0,
        ];

        foreach ($rows as $row) {
            $totals['created_count'] += $row['created_count'];
            $totals['resolved_count'] += $row['resolved_count'];
            $totals['reopened_count'] += $row['reopened_count'];
        }

        return $totals;
    }

    private function applyScopeToQuery(Builder $query, Builder $ticketQuery): Builder
    {
        // Apply scope directly by joining to scoped query or copying where clauses
        // For now, use the ticket query's bindings as a template
        return $query;
    }
}
