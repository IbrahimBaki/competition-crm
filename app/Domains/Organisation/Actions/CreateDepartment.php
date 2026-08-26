<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class CreateDepartment
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(array $data, ?Authenticatable $actor = null): Department
    {
        return \DB::transaction(function () use ($data, $actor) {
            $department = Department::create([
                'id' => Str::uuid(),
                'branch_id' => $data['branch_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => true,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.department.created',
                $department,
                null,
                $department->toArray()
            );

            return $department;
        });
    }
}
