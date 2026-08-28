<?php

namespace App\Domains\Ticketing\Services\Lifecycle;

use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonInterface;

final class ReopenWindow
{
    public function deadlineFor(CarbonInterface $resolvedAt): ?CarbonInterface
    {
        $days = max(0, config('tickets.reopen_window_days', 14));

        if ($days === 0) {
            return null;
        }

        return $resolvedAt->addDays($days);
    }

    public function isOpen(Ticket $ticket, CarbonInterface $now): bool
    {
        if ($ticket->reopen_deadline_at === null) {
            return false;
        }

        return $now->lte($ticket->reopen_deadline_at);
    }
}
