<?php

namespace App\Domains\Security\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::ADMIN_USERS_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function invite(User $user): Response
    {
        return $user->can(PermissionKey::ADMIN_USERS_INVITE)
            ? Response::allow()
            : Response::deny();
    }

    public function activate(User $user, User $target): Response
    {
        return $user->can(PermissionKey::ADMIN_USERS_ACTIVATE)
            ? Response::allow()
            : Response::deny();
    }

    public function deactivate(User $user, User $target): Response
    {
        return $user->can(PermissionKey::ADMIN_USERS_DEACTIVATE)
            ? Response::allow()
            : Response::deny();
    }
}
