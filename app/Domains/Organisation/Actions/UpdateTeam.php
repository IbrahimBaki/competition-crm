<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Team;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateTeam
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Team $team, array $data, ?Authenticatable $actor = null): Team
    {
        return \DB::transaction(function () use ($team, $data, $actor) {
            $before = $team->toArray();

            $team->update([
                'name' => $data['name'] ?? $team->name,
                'code' => $data['code'] ?? $team->code,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.team.updated',
                $team->refresh(),
                $before,
                $team->toArray()
            );

            return $team;
        });
    }
}
