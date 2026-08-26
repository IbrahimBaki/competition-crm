<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Domains\Ticketing\Exceptions\CannotMergeTicketIntoItselfException;
use App\Domains\Ticketing\Exceptions\TicketAlreadyMergedException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\Merge\TicketMergeRelationRegistry;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class MergeTickets
{
    public function __construct(
        private readonly TicketMergeRelationRegistry $mergeRegistry,
        private readonly RecordTicketEvent $recordEvent,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function __invoke(Ticket $source, Ticket $target, User $actor): Ticket
    {
        throw_if($source->id === $target->id, CannotMergeTicketIntoItselfException::class);
        throw_if($source->isMerged(), TicketAlreadyMergedException::class);

        if ($this->isChainMerge($source, $target)) {
            throw new CannotMergeTicketIntoItselfException('Merge cycle detected');
        }

        $closedStatus = TicketStatusDefinition::query()
            ->where('lifecycle_type', 'closed')
            ->firstOrFail();

        $target = \DB::transaction(function () use ($source, $target, $actor, $closedStatus) {
            $first = $source->id < $target->id ? $source : $target;
            $second = $source->id < $target->id ? $target : $source;

            Ticket::whereIn('id', [$first->id, $second->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $source->refresh();
            $target->refresh();

            throw_if($source->isMerged(), TicketAlreadyMergedException::class);

            $this->mergeRegistry->moveAll($source, $target);

            $now = now();

            $source->update([
                'ticket_status_id' => $closedStatus->id,
                'status' => 'closed',
                'status_changed_at' => $now,
                'merged_into_ticket_id' => $target->id,
                'merged_at' => $now,
            ]);

            $this->recordEvent->handle($source, TicketEventType::Merged, $actor, [
                'merged_into' => $target->uuid,
            ]);

            $this->recordEvent->handle($target, TicketEventType::Merged, $actor, [
                'merged_from' => $source->uuid,
            ]);

            $this->auditLogger->record($actor, 'ticket.merge', $source, [
                'source_uuid' => $source->uuid,
                'target_uuid' => $target->uuid,
            ], [
                'merged_into_ticket_id' => $target->id,
                'merged_at' => $now,
            ]);

            return $target->fresh();
        });

        return $target;
    }

    private function isChainMerge(Ticket $source, Ticket $target): bool
    {
        $current = $target;
        $hops = 0;
        $maxHops = 50;

        while ($current->merged_into_ticket_id !== null && $hops < $maxHops) {
            $current = Ticket::findOrFail($current->merged_into_ticket_id);
            if ($current->id === $source->id) {
                return true;
            }
            $hops++;
        }

        return false;
    }
}
