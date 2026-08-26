<?php

namespace App\Domains\Sla\Services;

use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use Carbon\CarbonImmutable;

/**
 * Implements SlaClockHooks using the SLA services.
 * This is the bridge from Ticketing → Sla domain.
 */
class SlaClockHooksBridge implements SlaClockHooks
{
    public function __construct(
        private readonly SlaPolicyResolver $policyResolver,
        private readonly SlaClockService $clockService,
        private readonly SlaEvaluator $evaluator,
    ) {}

    public function ticketCreated(Ticket $ticket): void
    {
        $this->clockService->start($ticket, SlaTargetType::FirstResponse);
        $this->clockService->start($ticket, SlaTargetType::Resolution);
    }

    public function firstAgentReplySent(Ticket $ticket, CarbonImmutable $at): void
    {
        $clock = $ticket->slaClocks()
            ->where('target_type', SlaTargetType::FirstResponse->value)
            ->first();

        if ($clock && $clock->completed_at === null) {
            $this->clockService->complete($clock, $at);
            $this->evaluator->evaluate($clock, $at);
        }
    }

    public function ticketResolved(Ticket $ticket, CarbonImmutable $at): void
    {
        $clock = $ticket->slaClocks()
            ->where('target_type', SlaTargetType::Resolution->value)
            ->first();

        if ($clock) {
            $this->clockService->complete($clock, $at);
            $this->evaluator->evaluate($clock, $at);
        }
    }

    public function statusChanged(
        Ticket $ticket,
        bool $stopsClock,
        ?int $statusId,
        CarbonImmutable $at,
    ): void {
        $clocks = $ticket->slaClocks()
            ->where('state', '!=', SlaClockState::Cancelled->value)
            ->whereIn('state', [SlaClockState::Running->value, SlaClockState::Paused->value])
            ->get();

        foreach ($clocks as $clock) {
            if ($stopsClock) {
                $this->clockService->pause($clock, $at, $statusId);
            } else {
                $this->clockService->resume($clock, $at);
            }

            $this->evaluator->evaluate($clock, $at);
        }
    }

    public function classificationChanged(Ticket $ticket, CarbonImmutable $at): void
    {
        $policy = $this->policyResolver->resolveFor($ticket);

        if (! $policy) {
            return;
        }

        $clocks = $ticket->slaClocks()
            ->where('state', '!=', SlaClockState::Breached->value)
            ->where('state', '!=', SlaClockState::Cancelled->value)
            ->get();

        foreach ($clocks as $clock) {
            $targetType = SlaTargetType::from($clock->target_type);
            $newTarget = $this->policyResolver->resolveTarget($policy, $ticket, $targetType);

            if ($newTarget && $newTarget->id !== $clock->sla_target_id) {
                $this->clockService->retarget($clock, $newTarget, $at);
            }

            $this->evaluator->evaluate($clock, $at);
        }
    }

    public function ticketCancelled(Ticket $ticket, string $reason, CarbonImmutable $at): void
    {
        $clocks = $ticket->slaClocks()
            ->where('state', '!=', SlaClockState::Cancelled->value)
            ->get();

        foreach ($clocks as $clock) {
            $this->clockService->cancel($clock, $at, $reason);
        }
    }

    public function ticketRestored(Ticket $ticket, CarbonImmutable $at): void
    {
        $clocks = $ticket->slaClocks()
            ->where('state', SlaClockState::Cancelled->value)
            ->get();

        foreach ($clocks as $clock) {
            $clock->update([
                'state' => SlaClockState::Running->value,
                'last_counted_at' => $at,
            ]);

            $this->evaluator->evaluate($clock, $at);
        }
    }
}
