<?php

namespace App\Domains\Sla\Services;

use App\Domains\Sla\Models\SlaBreach;
use App\Domains\Sla\Models\SlaBreachReason;
use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\TicketSlaClock;
use App\Domains\Ticketing\Models\TicketEventType;
use Carbon\CarbonImmutable;

/**
 * Evaluates whether a clock has reached the warning threshold or breached.
 * Records events and breach snapshots.
 */
class SlaEvaluator
{
    public function __construct(
        private readonly SlaClockService $clockService,
    ) {}

    public function evaluate(TicketSlaClock $clock, CarbonImmutable $asOf): void
    {
        if ($clock->state !== SlaClockState::Running->value) {
            return;
        }

        $position = $this->clockService->position($clock, $asOf);

        $this->checkWarning($clock, $position);
        $this->checkBreach($clock, $position, $asOf);
    }

    private function checkWarning(TicketSlaClock $clock, $position): void
    {
        if ($clock->warned_at !== null) {
            return;
        }

        $policy = $clock->policy;
        if (! $policy) {
            return;
        }

        $thresholdMinutes = (int) round(
            $clock->target_minutes * $policy->warning_threshold_percent / 100
        );

        if ($position->elapsedMinutes < $thresholdMinutes) {
            return;
        }

        $clock->update(['warned_at' => CarbonImmutable::now('UTC')]);

        $ticket = $clock->ticket;
        $ticket->recordEvent(TicketEventType::SlaWarningRaised);

        // TODO(story: notifications): dispatch notification with key 'sla.breach.warning'
    }

    private function checkBreach(
        TicketSlaClock $clock,
        $position,
        CarbonImmutable $asOf,
    ): void {
        if ($position->elapsedMinutes <= $clock->target_minutes) {
            return;
        }

        $clock->update([
            'state' => SlaClockState::Breached->value,
            'breached_at' => $asOf,
        ]);

        $ticket = $clock->ticket;
        $reason = SlaBreachReason::TargetExceeded;

        SlaBreach::firstOrCreate(
            [
                'ticket_id' => $ticket->id,
                'target_type' => $clock->target_type,
                'ticket_sla_clock_id' => $clock->id,
            ],
            [
                'priority' => $ticket->priority?->value,
                'target_minutes' => $clock->target_minutes,
                'due_at' => $clock->due_at,
                'breached_at' => $asOf,
                'actual_minutes' => $position->elapsedMinutes,
                'overdue_minutes' => $position->elapsedMinutes - $clock->target_minutes,
                'reason' => $reason->value,
            ]
        );

        $ticket->recordEvent(TicketEventType::SlaBreached);
    }
}
