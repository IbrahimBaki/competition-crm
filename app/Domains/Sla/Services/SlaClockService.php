<?php

namespace App\Domains\Sla\Services;

use App\Domains\Organisation\Services\WorkingTimeService;
use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\SlaPauseInterval;
use App\Domains\Sla\Models\SlaPosition;
use App\Domains\Sla\Models\SlaTarget;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Sla\Models\TicketSlaClock;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;

/**
 * Manages SLA clock lifecycle and time calculations.
 *
 * All arithmetic delegates to WorkingTimeService; this file contains no manual
 * weekend/holiday logic or now()->diff* calls. All instants are UTC.
 * Persisted values (elapsed, due_at) are immutable snapshots.
 */
class SlaClockService
{
    public function __construct(
        private readonly WorkingTimeService $workingTime,
    ) {}

    public function start(Ticket $ticket, SlaTargetType $type): ?TicketSlaClock
    {
        $resolver = new SlaPolicyResolver;
        $policy = $resolver->resolveFor($ticket);

        if (! $policy) {
            return null;
        }

        $target = $resolver->resolveTarget($policy, $ticket, $type);

        if (! $target) {
            return null;
        }

        $branch = $ticket->department->branch;
        $now = CarbonImmutable::now('UTC');

        $clock = TicketSlaClock::firstOrCreate(
            [
                'ticket_id' => $ticket->id,
                'target_type' => $type->value,
            ],
            [
                'sla_policy_id' => $policy->id,
                'sla_target_id' => $target->id,
                'target_minutes' => $target->minutes,
                'started_at' => $now,
                'last_counted_at' => $now,
                'elapsed_minutes' => 0,
                'due_at' => $this->workingTime->addWorkingMinutes($branch, $now, $target->minutes),
                'state' => SlaClockState::Running->value,
            ]
        );

        return $clock->fresh();
    }

    public function accrue(TicketSlaClock $clock, CarbonImmutable $upTo): void
    {
        if ($clock->state === SlaClockState::Paused->value) {
            return;
        }

        $ticket = $clock->ticket;
        $branch = $ticket->department->branch;

        $additional = $this->workingTime->elapsedWorkingMinutes(
            $branch,
            $clock->last_counted_at,
            $upTo
        );

        $clock->update([
            'elapsed_minutes' => $clock->elapsed_minutes + $additional,
            'last_counted_at' => $upTo,
        ]);
    }

    public function pause(
        TicketSlaClock $clock,
        CarbonImmutable $at,
        ?int $statusId = null,
        ?string $reason = null,
    ): void {
        $this->accrue($clock, $at);

        $clock->update([
            'state' => SlaClockState::Paused->value,
            'paused_at' => $at,
        ]);

        SlaPauseInterval::create([
            'ticket_sla_clock_id' => $clock->id,
            'ticket_status_id' => $statusId,
            'paused_at' => $at,
            'reason' => $reason,
        ]);
    }

    public function resume(TicketSlaClock $clock, CarbonImmutable $at): void
    {
        $interval = SlaPauseInterval::where('ticket_sla_clock_id', $clock->id)
            ->whereNull('resumed_at')
            ->first();

        if (! $interval) {
            return;
        }

        $ticket = $clock->ticket;
        $branch = $ticket->department->branch;

        $pausedMinutes = $this->workingTime->elapsedWorkingMinutes(
            $branch,
            $interval->paused_at,
            $at
        );

        $interval->update([
            'resumed_at' => $at,
            'paused_working_minutes' => $pausedMinutes,
        ]);

        $remaining = max(0, $clock->target_minutes - $clock->elapsed_minutes);
        $newDueAt = $remaining > 0
            ? $this->workingTime->addWorkingMinutes($branch, $at, $remaining)
            : $at;

        $clock->update([
            'state' => SlaClockState::Running->value,
            'paused_at' => null,
            'last_counted_at' => $at,
            'due_at' => $newDueAt,
        ]);
    }

    public function retarget(
        TicketSlaClock $clock,
        SlaPolicy $policy,
        SlaTarget $target,
        CarbonImmutable $at,
    ): void {
        if ($clock->state === SlaClockState::Breached->value) {
            return;
        }

        $this->accrue($clock, $at);

        $ticket = $clock->ticket;
        $branch = $ticket->department->branch;

        $remaining = max(0, $target->minutes - $clock->elapsed_minutes);
        $newDueAt = $remaining > 0
            ? $this->workingTime->addWorkingMinutes($branch, $at, $remaining)
            : $at;

        $clock->update([
            'sla_policy_id' => $policy->id,
            'sla_target_id' => $target->id,
            'target_minutes' => $target->minutes,
            'due_at' => $newDueAt,
        ]);
    }

    public function complete(TicketSlaClock $clock, CarbonImmutable $at): void
    {
        $this->accrue($clock, $at);

        $newState = $clock->state === SlaClockState::Breached->value
            ? SlaClockState::Breached->value
            : SlaClockState::Met->value;

        $clock->update([
            'completed_at' => $at,
            'state' => $newState,
        ]);
    }

    public function cancel(
        TicketSlaClock $clock,
        CarbonImmutable $at,
        string $reason,
    ): void {
        $clock->update([
            'state' => SlaClockState::Cancelled->value,
        ]);
    }

    public function reset(
        TicketSlaClock $clock,
        CarbonImmutable $at,
        string $reason,
    ): void {
        $ticket = $clock->ticket;
        $branch = $ticket->department->branch;

        $resolver = new SlaPolicyResolver;
        $policy = $resolver->resolveFor($ticket);

        if (! $policy || ! $clock->target()) {
            return;
        }

        $target = $clock->target;
        $dueAt = $this->workingTime->addWorkingMinutes($branch, $at, $target->minutes);

        $clock->update([
            'started_at' => $at,
            'last_counted_at' => $at,
            'elapsed_minutes' => 0,
            'due_at' => $dueAt,
            'reset_at' => $at,
            'warned_at' => null,
            'state' => SlaClockState::Running->value,
        ]);
    }

    public function position(
        TicketSlaClock $clock,
        CarbonImmutable $asOf,
    ): SlaPosition {
        $elapsed = $clock->elapsed_minutes;

        if ($clock->state === SlaClockState::Running->value) {
            $ticket = $clock->ticket;
            $branch = $ticket->department->branch;

            $additional = $this->workingTime->elapsedWorkingMinutes(
                $branch,
                $clock->last_counted_at,
                $asOf
            );

            $elapsed += $additional;
        }

        $remaining = $clock->target_minutes - $elapsed;

        return new SlaPosition(
            targetType: SlaTargetType::from($clock->target_type),
            state: SlaClockState::from($clock->state),
            dueAt: $clock->due_at,
            targetMinutes: $clock->target_minutes,
            elapsedMinutes: $elapsed,
            remainingMinutes: $remaining,
            warningFired: $clock->warned_at !== null,
            pausedAt: $clock->paused_at,
        );
    }
}
