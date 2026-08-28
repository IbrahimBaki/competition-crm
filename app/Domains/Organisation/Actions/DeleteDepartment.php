<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Exceptions\DepartmentInUseException;
use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Services\DepartmentUsageChecker;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Soft-deletes a department. Guarded by the same usage rule as deactivation:
 * a department still holding open tickets cannot be removed.
 */
class DeleteDepartment
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly DepartmentUsageChecker $usageChecker,
    ) {}

    public function execute(Department $department, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($department, $actor) {
            $openTicketCount = $this->usageChecker->openTicketCount($department->id);
            if ($openTicketCount > 0) {
                throw new DepartmentInUseException($openTicketCount);
            }

            $before = $department->toArray();
            $department->delete();

            $this->auditLogger->record($actor, 'organisation.department.deleted', $department, $before, null);
        });
    }
}
