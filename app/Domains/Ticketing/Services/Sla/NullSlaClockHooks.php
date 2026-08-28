<?php

namespace App\Domains\Ticketing\Services\Sla;

use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;

/**
 * No-op implementation of SlaClockHooks.
 * Used as fallback when SLA tables don't exist (e.g., migrate:fresh before migrate).
 */
class NullSlaClockHooks implements SlaClockHooks
{
    public function ticketCreated(Ticket $ticket): void {}

    public function firstAgentReplySent(Ticket $ticket, CarbonImmutable $at): void {}

    public function ticketResolved(Ticket $ticket, CarbonImmutable $at): void {}

    public function statusChanged(
        Ticket $ticket,
        bool $stopsClock,
        ?int $statusId,
        CarbonImmutable $at,
    ): void {}

    public function classificationChanged(Ticket $ticket, CarbonImmutable $at): void {}

    public function ticketCancelled(Ticket $ticket, string $reason, CarbonImmutable $at): void {}

    public function ticketRestored(Ticket $ticket, CarbonImmutable $at): void {}
}
