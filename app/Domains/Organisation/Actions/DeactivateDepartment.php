<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Exceptions\DepartmentInUseException;
use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Services\DepartmentUsageChecker;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class DeactivateDepartment
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly DepartmentUsageChecker $usageChecker) {}

    public function execute(Department $department, array $data = [], ?Authenticatable $actor = null): Department
    {
        return \DB::transaction(function () use ($department, $data, $actor) {
            $openTicketCount = $this->usageChecker->openTicketCount($department->id);
            if ($openTicketCount > 0) {
                throw new DepartmentInUseException($openTicketCount);
            }

            $before = $department->toArray();
            $department->update(['is_active' => false]);
            $department->refresh();

            $after = $department->toArray();
            if (isset($data['reassign_to_department_id'])) {
                $after['reassign_intent'] = $data['reassign_to_department_id'];
            }

            $this->auditLogger->record($actor, 'organisation.department.deactivated', $department, $before, $after);

            return $department;
        });
    }
}
