<?php

namespace App\Domains\Organisation\Policies;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BranchPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_VIEW_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, Branch $branch): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_VIEW_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Branch $branch): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, Branch $branch): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function activate(User $user, Branch $branch): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function deactivate(User $user, Branch $branch): Response
    {
        return $user->can(PermissionKey::ORG_BRANCHES_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }
}
