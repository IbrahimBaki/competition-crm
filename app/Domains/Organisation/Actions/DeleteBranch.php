<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Exceptions\BranchHasActiveDepartmentsException;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Services\BranchUsageChecker;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Soft-deletes a branch. Guarded by the same usage rule as deactivation:
 * a branch still holding active departments cannot be removed.
 */
class DeleteBranch
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly BranchUsageChecker $usageChecker,
    ) {}

    public function execute(Branch $branch, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($branch, $actor) {
            $activeDepartmentCount = $this->usageChecker->activeDepartmentCount($branch->id);
            if ($activeDepartmentCount > 0) {
                throw new BranchHasActiveDepartmentsException($activeDepartmentCount);
            }

            $before = $branch->toArray();
            $branch->delete();

            $this->auditLogger->record($actor, 'organisation.branch.deleted', $branch, $before, null);
        });
    }
}
