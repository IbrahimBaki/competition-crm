<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class DetachRoleFromUser
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(?Authenticatable $actor, User $user, int $roleId): void
    {
        $before = [
            'roles' => $user->roles()->pluck('id')->toArray(),
        ];

        $user->roles()->detach($roleId);

        $after = [
            'roles' => $user->fresh()->roles()->pluck('id')->toArray(),
        ];

        $this->auditLogger->record($actor, 'role_detached', $user, $before, $after);
    }
}
