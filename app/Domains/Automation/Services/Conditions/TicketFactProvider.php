<?php

namespace App\Domains\Automation\Services\Conditions;

use App\Domains\Organisation\Services\WorkingTimeService;
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
        $ticket->load(['currentDepartment', 'assignee']);

        $businessMinutesSinceCreation = $this->workingTimeService->elapsedWorkingMinutes(
            $ticket->created_at,
            $asOf,
            $ticket->branch,
        );

        $lastCustomerMessage = $ticket->messages()
            ->where('actor_type', 'customer')
            ->latest('created_at')
            ->first();

        $minutesSinceLastCustomerMessage = $lastCustomerMessage
            ? $this->workingTimeService->elapsedWorkingMinutes(
                $lastCustomerMessage->created_at,
                $asOf,
                $ticket->branch,
            )
            : $businessMinutesSinceCreation;

        $lastAgentMessage = $ticket->messages()
            ->where('actor_type', '!=', 'customer')
            ->latest('created_at')
            ->first();

        $minutesSinceLastAgentMessage = $lastAgentMessage
            ? $this->workingTimeService->elapsedWorkingMinutes(
                $lastAgentMessage->created_at,
                $asOf,
                $ticket->branch,
            )
            : $businessMinutesSinceCreation;

        $activeClock = $ticket->slaClocks()
            ->whereIn('state', ['running', 'breached'])
            ->first();

        $slaPosition = $activeClock
            ? $this->slaClockService->position($activeClock, $asOf)
            : null;

        $slaBreached = $activeClock && $activeClock->state === 'breached';

        return [
            'status' => $ticket->status,
            'priority' => $ticket->priority->value ?? null,
            'department_id' => $ticket->department_id,
            'category_id' => $ticket->category_id,
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
