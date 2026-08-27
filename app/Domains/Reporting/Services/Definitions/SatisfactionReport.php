<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Portal\Models\TicketFeedbackInvitation;
use App\Domains\Reporting\Services\Filters\ReportFilter;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\DB;

class SatisfactionReport implements ReportDefinition
{
    public function key(): string
    {
        return 'satisfaction';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function columns(): array
    {
        return [
            'invitations_sent',
            'responses_received',
            'response_rate_pct',
            'average_score',
            'score_1_count',
            'score_2_count',
            'score_3_count',
            'score_4_count',
            'score_5_count',
        ];
    }

    public function build(ReportFilter $filter, Closure $scope): ReportResult
    {
        $invitationsSent = TicketFeedbackInvitation::whereBetween('created_at', [
            $filter->from->toDateTimeString(),
            $filter->to->toDateTimeString(),
        ])->count();

        $feedbackQuery = DB::table('ticket_feedback')
            ->whereBetween('ticket_feedback.created_at', [
                $filter->from->toDateTimeString(),
                $filter->to->toDateTimeString(),
            ]);

        $responsesReceived = (clone $feedbackQuery)->count();
        $responseRate = $invitationsSent > 0 ? round(($responsesReceived / $invitationsSent) * 100, 2) : 0;
        $averageScore = (clone $feedbackQuery)->avg('ticket_feedback.score') ?? 0;

        $scoreDistribution = [
            'score_1_count' => (clone $feedbackQuery)->where('ticket_feedback.score', 1)->count(),
            'score_2_count' => (clone $feedbackQuery)->where('ticket_feedback.score', 2)->count(),
            'score_3_count' => (clone $feedbackQuery)->where('ticket_feedback.score', 3)->count(),
            'score_4_count' => (clone $feedbackQuery)->where('ticket_feedback.score', 4)->count(),
            'score_5_count' => (clone $feedbackQuery)->where('ticket_feedback.score', 5)->count(),
        ];

        $rows = [
            [
                'invitations_sent' => $invitationsSent,
                'responses_received' => $responsesReceived,
                'response_rate_pct' => $responseRate,
                'average_score' => round($averageScore, 2),
                ...$scoreDistribution,
            ],
        ];

        return new ReportResult(
            rows: $rows,
            totals: $rows[0],
            generatedAt: CarbonImmutable::now('UTC'),
            timezone: $filter->timezone,
        );
    }
}
