<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivateBranch
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Branch $branch, ?Authenticatable $actor = null): Branch
    {
        return \DB::transaction(function () use ($branch, $actor) {
            $before = $branch->toArray();
            $branch->update(['is_active' => true]);
            $branch->refresh();

            $this->auditLogger->record($actor, 'organisation.branch.activated', $branch, $before, $branch->toArray());

            return $branch;
        });
    }
}
