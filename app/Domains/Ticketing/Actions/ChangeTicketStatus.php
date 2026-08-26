<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\IllegalTicketTransitionException;
use App\Domains\Ticketing\Exceptions\TicketIsReadOnlyException;
use App\Domains\Ticketing\Exceptions\TicketReopenWindowExpiredException;
use App\Domains\Ticketing\Exceptions\TicketTransitionReasonRequiredException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\Lifecycle\ReopenWindow;
use App\Domains\Ticketing\Services\Lifecycle\TicketTransitionMap;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChangeTicketStatus
{
    public function __construct(
        private readonly RecordTicketEvent $recordEvent,
        private readonly TicketTransitionMap $transitionMap,
        private readonly ReopenWindow $reopenWindow,
    ) {}

    public function __invoke(
        Ticket $ticket,
        TicketStatusDefinition $target,
        User $actor,
        ?string $reason = null,
    ): Ticket {
        throw_if($ticket->isMerged(), TicketIsReadOnlyException::class);

        $from = $ticket->lifecycleType();
        $to = $target->lifecycle_type;

        if ($from === $to && $ticket->ticket_status_id === $target->id) {
            return $ticket;
        }

        throw_if(
            ! $this->transitionMap->allows($from, $to),
            IllegalTicketTransitionException::class,
        );

        throw_if(
            $this->transitionMap->requiresReason($from, $to) && blank($reason),
            TicketTransitionReasonRequiredException::class,
        );

        if ($from->value === 'resolved' && $to->value === 'open') {
            throw_if(
                ! $this->reopenWindow->isOpen($ticket, now()),
                TicketReopenWindowExpiredException::class,
            );
        }

        $ticket = DB::transaction(function () use ($ticket, $target, $from, $to, $reason, $actor) {
            Ticket::where('id', $ticket->id)->lockForUpdate()->first();
            $ticket->refresh();

            $now = now();

            $ticket->update([
                'ticket_status_id' => $target->id,
                'status' => $to->value,
                'status_changed_at' => $now,
                'resolved_at' => $to->value === 'resolved' ? $now : null,
                'reopen_deadline_at' => $to->value === 'resolved' ? $this->reopenWindow->deadlineFor($now) : null,
                'spam_marked_at' => $to->value === 'spam' ? $now : null,
            ]);

            $this->recordEvent->handle($ticket, TicketEventType::StatusChanged, $actor, [
                'from' => $from->value,
                'to' => $to->value,
                'reason' => $reason,
            ]);

            return $ticket->fresh();
        });

        return $ticket;
    }
}
