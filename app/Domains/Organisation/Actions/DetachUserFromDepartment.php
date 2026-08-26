<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class DetachUserFromDepartment
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(User $user, string $departmentId, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($user, $departmentId, $actor) {
            $user->departments()->detach($departmentId);

            $this->auditLogger->record(
                $actor,
                'organisation.user_department.detached',
                $user,
                ['department_id' => $departmentId],
                null
            );
        });
    }
}
