<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Team;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivateTeam
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Team $team, ?Authenticatable $actor = null): Team
    {
        return \DB::transaction(function () use ($team, $actor) {
            $before = $team->toArray();
            $team->update(['is_active' => true]);
            $team->refresh();

            $this->auditLogger->record($actor, 'organisation.team.activated', $team, $before, $team->toArray());

            return $team;
        });
    }
}
