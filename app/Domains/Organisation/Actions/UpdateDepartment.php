<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateDepartment
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Department $department, array $data, ?Authenticatable $actor = null): Department
    {
        return \DB::transaction(function () use ($department, $data, $actor) {
            $before = $department->toArray();

            $department->update([
                'name' => $data['name'] ?? $department->name,
                'code' => $data['code'] ?? $department->code,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.department.updated',
                $department->refresh(),
                $before,
                $department->toArray()
            );

            return $department;
        });
    }
}
