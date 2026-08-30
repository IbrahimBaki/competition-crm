<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Team;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Soft-deletes a team. Teams carry no usage guard, matching DeactivateTeam.
 */
class DeleteTeam
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(Team $team, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($team, $actor) {
            $before = $team->toArray();
            $team->delete();

            $this->auditLogger->record($actor, 'organisation.team.deleted', $team, $before, null);
        });
    }
}
