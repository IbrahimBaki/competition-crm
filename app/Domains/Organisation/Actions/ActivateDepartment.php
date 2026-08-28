<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivateDepartment
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Department $department, ?Authenticatable $actor = null): Department
    {
        return \DB::transaction(function () use ($department, $actor) {
            $before = $department->toArray();
            $department->update(['is_active' => true]);
            $department->refresh();

            $this->auditLogger->record($actor, 'organisation.department.activated', $department, $before, $department->toArray());

            return $department;
        });
    }
}
