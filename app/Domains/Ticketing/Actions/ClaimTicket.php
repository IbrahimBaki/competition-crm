<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\TicketAlreadyClaimedException;
use App\Domains\Ticketing\Exceptions\TicketIsReadOnlyException;
use App\Domains\Ticketing\Exceptions\TicketVersionConflictException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\Concurrency\TicketVersionGuard;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClaimTicket
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly TicketVersionGuard $versionGuard,
    ) {}

    public function handle(Ticket $ticket, User $claimant, ?int $expectedVersion = null): Ticket
    {
        if ($ticket->isMerged() || $ticket->isSpam()) {
            throw new TicketIsReadOnlyException('Ticket is read-only');
        }

        // If already claimed by the claimant, it's idempotent - no event
        if ($ticket->assigned_user_id === $claimant->id) {
            return $ticket;
        }

        // If claimed by someone else, conflict
        if ($ticket->assigned_user_id !== null) {
            throw new TicketAlreadyClaimedException('Ticket is already claimed');
        }

        $ticket = DB::transaction(function () use ($ticket, $claimant, $expectedVersion) {
            // Refresh to get current state
            $ticket->refresh();

            // Check version conflict if expected
            if ($expectedVersion !== null && $ticket->version !== $expectedVersion) {
                throw new TicketVersionConflictException(
                    $ticket->version,
                    $ticket->assignee?->uuid,
                    $ticket->department?->uuid ?? $ticket->department_id,
                );
            }

            // Attempt to claim atomically: only succeed if unowned
            $updated = DB::table('tickets')
                ->where('id', $ticket->id)
                ->whereNull('assigned_user_id')
                ->where('version', $ticket->version)
                ->update([
                    'assigned_user_id' => $claimant->id,
                    'assigned_at' => now(),
                    'version' => DB::raw('version + 1'),
                ]);

            if ($updated === 0) {
                // Re-read to determine the conflict type
                $current = $ticket->fresh();
                if ($current->assigned_user_id !== null) {
                    throw new TicketAlreadyClaimedException('Ticket is already claimed');
                }
                throw new TicketVersionConflictException(
                    $current->version,
                    $current->assignee?->uuid,
                    $current->department?->uuid ?? $current->department_id,
                );
            }

            return $ticket->fresh();
        });

        $this->recordEvent->handle($ticket, TicketEventType::Claimed, $claimant, [
            'user_uuid' => $claimant->uuid,
            'previous_user_uuid' => null,
        ]);

        return $ticket;
    }
}
