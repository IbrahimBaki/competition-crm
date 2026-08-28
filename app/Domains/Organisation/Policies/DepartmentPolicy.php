<?php

namespace App\Domains\Organisation\Policies;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_VIEW_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, Department $department): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_VIEW_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Department $department): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, Department $department): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function activate(User $user, Department $department): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function deactivate(User $user, Department $department): Response
    {
        return $user->can(PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }
}
