<?php

namespace App\Domains\Ticketing\Services\Sla;

use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;

/**
 * Hook interface for SLA lifecycle events.
 * Ticketing calls these methods; Sla implements them.
 * No reverse dependency: Ticketing → Sla is via this interface only.
 */
interface SlaClockHooks
{
    public function ticketCreated(Ticket $ticket): void;

    public function firstAgentReplySent(Ticket $ticket, CarbonImmutable $at): void;

    public function ticketResolved(Ticket $ticket, CarbonImmutable $at): void;

    public function statusChanged(
        Ticket $ticket,
        bool $stopsClock,
        ?int $statusId,
        CarbonImmutable $at,
    ): void;

    public function classificationChanged(Ticket $ticket, CarbonImmutable $at): void;

    public function ticketCancelled(Ticket $ticket, string $reason, CarbonImmutable $at): void;

    public function ticketRestored(Ticket $ticket, CarbonImmutable $at): void;
}
