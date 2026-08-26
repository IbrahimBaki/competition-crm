<?php

namespace App\Domains\Ticketing\Services\Concurrency;

use App\Domains\Ticketing\Exceptions\TicketVersionConflictException;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketVersionGuard
{
    /**
     * Throws TicketVersionConflictException when $expectedVersion is provided
     * and does not match the ticket's current version.
     */
    public function assert(Ticket $ticket, ?int $expectedVersion): void
    {
        if ($expectedVersion === null) {
            return;
        }

        if ($ticket->version !== $expectedVersion) {
            throw new TicketVersionConflictException(
                $ticket->version,
                $ticket->assignee?->uuid,
                $ticket->department?->uuid ?? $ticket->department_id,
            );
        }
    }

    /**
     * Atomically increments the version and updates the given attributes.
     * Uses a conditional UPDATE ... WHERE id = ? AND version = ? so
     * two simultaneous requests cannot both succeed. A read-then-write
     * with PHP-side comparison is not acceptable for the concurrency
     * guarantee.
     */
    public function bump(Ticket $ticket, array $attributes = [], ?int $expectedVersion = null): Ticket
    {
        if ($expectedVersion === null) {
            $expectedVersion = $ticket->version;
        }

        $attributes['version'] = DB::raw('version + 1');

        $updated = DB::table('tickets')
            ->where('id', $ticket->id)
            ->where('version', $expectedVersion)
            ->update($attributes);

        if ($updated === 0) {
            $current = $ticket->fresh();
            throw new TicketVersionConflictException(
                $current->version,
                $current->assignee?->uuid,
                $current->department?->uuid ?? $current->department_id,
            );
        }

        return $ticket->fresh();
    }
}
