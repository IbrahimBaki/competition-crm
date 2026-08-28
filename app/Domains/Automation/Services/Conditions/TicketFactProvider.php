<?php

namespace App\Domains\Automation\Services\Conditions;

use App\Domains\Organisation\Services\WorkingTimeService;
use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Services\SlaClockService;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;

final class TicketFactProvider
{
    public function __construct(
        private readonly WorkingTimeService $workingTimeService,
        private readonly SlaClockService $slaClockService,
    ) {}

    public function provide(Ticket $ticket, ?CarbonImmutable $asOf = null): array
    {
        $asOf ??= CarbonImmutable::now('UTC');
        $ticket->load(['branch', 'department', 'assignee']);

        $businessMinutesSinceCreation = $this->workingTimeService->elapsedWorkingMinutes(
            $ticket->branch,
            $ticket->created_at,
            $asOf,
        );

        $lastCustomerMessage = $ticket->messages()
            ->where('author_type', 'customer')
            ->latest('created_at')
            ->first();

        $minutesSinceLastCustomerMessage = $lastCustomerMessage
            ? $this->workingTimeService->elapsedWorkingMinutes(
                $ticket->branch,
                $lastCustomerMessage->created_at,
                $asOf,
            )
            : $businessMinutesSinceCreation;

        $lastAgentMessage = $ticket->messages()
            ->where('author_type', '!=', 'customer')
            ->latest('created_at')
            ->first();

        $minutesSinceLastAgentMessage = $lastAgentMessage
            ? $this->workingTimeService->elapsedWorkingMinutes(
                $ticket->branch,
                $lastAgentMessage->created_at,
                $asOf,
            )
            : $businessMinutesSinceCreation;

        $activeClock = $ticket->slaClocks()
            ->whereIn('state', ['running', 'breached'])
            ->first();

        $slaPosition = $activeClock
            ? $this->slaClockService->position($activeClock, $asOf)
            : null;

        // `state` is cast to the SlaClockState enum, so comparing against
        // a raw string was always false — every ticket read as "not
        // breached" to the automation engine regardless of actual state.
        $slaBreached = $activeClock && $activeClock->state === SlaClockState::Breached;

        return [
            'status' => $ticket->status,
            'priority' => $ticket->priority->value ?? null,
            'department_id' => $ticket->department_id,
            'category_id' => $ticket->ticket_category_id,
            'assignee_id' => $ticket->assigned_user_id,
            'tags' => $ticket->tags->pluck('name')->toArray(),
            'age_business_minutes' => $businessMinutesSinceCreation,
            'minutes_since_last_customer_message' => $minutesSinceLastCustomerMessage,
            'minutes_since_last_agent_message' => $minutesSinceLastAgentMessage,
            'sla_position' => $slaPosition,
            'sla_breached' => $slaBreached,
        ];
    }
}
