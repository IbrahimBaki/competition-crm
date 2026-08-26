<?php

namespace App\Domains\Security\Policies;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::ADMIN_ROLES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, Role $role): Response
    {
        return $user->can(PermissionKey::ADMIN_ROLES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::ADMIN_ROLES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Role $role): Response
    {
        return $user->can(PermissionKey::ADMIN_ROLES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, Role $role): Response
    {
        if ($role->is_system) {
            return Response::deny('System roles cannot be deleted.');
        }

        return $user->can(PermissionKey::ADMIN_ROLES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }
}
