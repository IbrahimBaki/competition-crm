<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEvent;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketStatus;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\DB;

class AgentPerformanceReport implements ReportDefinition
{
    public function key(): string
    {
        return 'agent_performance';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function columns(): array
    {
        return [
            'agent_uuid',
            'agent_name',
            'assigned',
            'resolved',
            'first_response_met',
            'resolution_met',
            'avg_csat',
        ];
    }

    public function build(ReportFilter $filter, Closure $scope): ReportResult
    {
        // If filtered to specific agent, use that; otherwise get all agents who handled tickets in range
        $agentIds = $this->getAgentIds($filter);

        $rows = [];
        foreach ($agentIds as $agentId) {
            $agent = User::find($agentId);
            if (! $agent) {
                continue;
            }

            $assigned = Ticket::where('assigned_user_id', $agentId)
                ->whereBetween('assigned_at', [
                    $filter->from->toDateTimeString(),
                    $filter->to->toDateTimeString(),
                ])
                ->count();

            $resolved = TicketEvent::where('actor_user_id', $agentId)
                ->where('type', TicketEventType::StatusChanged->value)
                ->where('payload->to', TicketStatus::Resolved->value)
                ->whereBetween('created_at', [
                    $filter->from->toDateTimeString(),
                    $filter->to->toDateTimeString(),
                ])
                ->count();

            // First response SLA
            $firstResponseMet = DB::table('ticket_sla_clocks')
                ->join('tickets', 'tickets.id', '=', 'ticket_sla_clocks.ticket_id')
                ->where('tickets.assigned_user_id', $agentId)
                ->where('ticket_sla_clocks.target_type', SlaTargetType::FirstResponse->value)
                ->where('ticket_sla_clocks.state', SlaClockState::Met->value)
                ->whereBetween('ticket_sla_clocks.created_at', [
                    $filter->from->toDateTimeString(),
                    $filter->to->toDateTimeString(),
                ])
                ->count();

            // Resolution SLA
            $resolutionMet = DB::table('ticket_sla_clocks')
                ->join('tickets', 'tickets.id', '=', 'ticket_sla_clocks.ticket_id')
                ->where('tickets.assigned_user_id', $agentId)
                ->where('ticket_sla_clocks.target_type', SlaTargetType::Resolution->value)
                ->where('ticket_sla_clocks.state', SlaClockState::Met->value)
                ->whereBetween('ticket_sla_clocks.created_at', [
                    $filter->from->toDateTimeString(),
                    $filter->to->toDateTimeString(),
                ])
                ->count();

            // CSAT average
            $avgCsat = DB::table('ticket_feedback')
                ->join('tickets', 'tickets.id', '=', 'ticket_feedback.ticket_id')
                ->where('tickets.assigned_user_id', $agentId)
                ->whereBetween('ticket_feedback.created_at', [
                    $filter->from->toDateTimeString(),
                    $filter->to->toDateTimeString(),
                ])
                ->avg('ticket_feedback.score') ?? 0;

            $rows[] = [
                'agent_uuid' => $agent->uuid,
                'agent_name' => $agent->name,
                'assigned' => $assigned,
                'resolved' => $resolved,
                'first_response_met' => $firstResponseMet,
                'resolution_met' => $resolutionMet,
                'avg_csat' => round($avgCsat, 2),
            ];
        }

        $totals = $this->calculateTotals($rows);

        return new ReportResult(
            rows: $rows,
            totals: $totals,
            generatedAt: CarbonImmutable::now('UTC'),
            timezone: $filter->timezone,
        );
    }

    /**
     * @return int[]
     */
    private function getAgentIds(ReportFilter $filter): array
    {
        if ($filter->agentId) {
            return [$filter->agentId];
        }

        return Ticket::whereNotNull('assigned_user_id')
            ->whereBetween('assigned_at', [
                $filter->from->toDateTimeString(),
                $filter->to->toDateTimeString(),
            ])
            ->distinct()
            ->pluck('assigned_user_id')
            ->all();
    }

    /**
     * @param  array<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function calculateTotals(array $rows): array
    {
        $totals = [
            'assigned' => 0,
            'resolved' => 0,
            'first_response_met' => 0,
            'resolution_met' => 0,
            'avg_csat' => 0,
        ];

        if (empty($rows)) {
            return $totals;
        }

        foreach ($rows as $row) {
            $totals['assigned'] += $row['assigned'];
            $totals['resolved'] += $row['resolved'];
            $totals['first_response_met'] += $row['first_response_met'];
            $totals['resolution_met'] += $row['resolution_met'];
            $totals['avg_csat'] += $row['avg_csat'];
        }

        $totals['avg_csat'] = round($totals['avg_csat'] / count($rows), 2);

        return $totals;
    }
}
