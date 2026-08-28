<?php

namespace App\Domains\Sla\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Domains\Sla\Models\TicketSlaClock;
use App\Models\User;

class ResetTicketSla
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(TicketSlaClock $clock, User $actor, string $reason): TicketSlaClock
    {
        $before = [
            'paused_at' => $clock->paused_at?->toIso8601String(),
            'due_at' => $clock->due_at?->toIso8601String(),
        ];

        $clock->update([
            'paused_at' => null,
            'due_at' => $clock->calculateNewDue(),
        ]);

        $this->auditLogger->record(
            $actor,
            'sla.reset',
            $clock->ticket,
            $before,
            [
                'paused_at' => $clock->paused_at?->toIso8601String(),
                'due_at' => $clock->due_at?->toIso8601String(),
                'reason' => $reason,
            ]
        );

        return $clock->fresh();
    }
}
