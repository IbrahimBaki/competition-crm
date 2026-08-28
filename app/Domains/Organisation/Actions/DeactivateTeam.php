<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Team;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class DeactivateTeam
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Team $team, ?Authenticatable $actor = null): Team
    {
        return \DB::transaction(function () use ($team, $actor) {
            $before = $team->toArray();
            $team->update(['is_active' => false]);
            $team->refresh();

            $this->auditLogger->record($actor, 'organisation.team.deactivated', $team, $before, $team->toArray());

            return $team;
        });
    }
}
