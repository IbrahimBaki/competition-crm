<?php

namespace App\Domains\Security\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AuditLogPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::ADMIN_AUDIT_VIEW)
            ? Response::allow()
            : Response::deny();
    }
}
