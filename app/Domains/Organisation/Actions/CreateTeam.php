<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Team;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class CreateTeam
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(array $data, ?Authenticatable $actor = null): Team
    {
        return \DB::transaction(function () use ($data, $actor) {
            $team = Team::create([
                'id' => Str::uuid(),
                'department_id' => $data['department_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => true,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.team.created',
                $team,
                null,
                $team->toArray()
            );

            return $team;
        });
    }
}
