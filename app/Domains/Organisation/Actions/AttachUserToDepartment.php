<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class AttachUserToDepartment
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(User $user, string $departmentId, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($user, $departmentId, $actor) {
            if (! $user->departments()->where('department_id', $departmentId)->exists()) {
                $user->departments()->attach($departmentId, ['created_at' => now(), 'updated_at' => now()]);
            }

            $this->auditLogger->record(
                $actor,
                'organisation.user_department.attached',
                $user,
                null,
                ['department_id' => $departmentId]
            );
        });
    }
}
