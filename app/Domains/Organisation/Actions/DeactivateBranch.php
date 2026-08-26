<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Exceptions\BranchHasActiveDepartmentsException;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Services\BranchUsageChecker;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class DeactivateBranch
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly BranchUsageChecker $usageChecker) {}

    public function execute(Branch $branch, ?Authenticatable $actor = null): Branch
    {
        return \DB::transaction(function () use ($branch, $actor) {
            $activeDepartmentCount = $this->usageChecker->activeDepartmentCount($branch->id);
            if ($activeDepartmentCount > 0) {
                throw new BranchHasActiveDepartmentsException($activeDepartmentCount);
            }

            $before = $branch->toArray();
            $branch->update(['is_active' => false]);
            $branch->refresh();

            $this->auditLogger->record($actor, 'organisation.branch.deactivated', $branch, $before, $branch->toArray());

            return $branch;
        });
    }
}
