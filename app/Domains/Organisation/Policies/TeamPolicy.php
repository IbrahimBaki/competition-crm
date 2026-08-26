<?php

namespace App\Domains\Organisation\Policies;

use App\Domains\Organisation\Models\Team;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_VIEW_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, Team $team): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_VIEW_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Team $team): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, Team $team): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function activate(User $user, Team $team): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }

    public function deactivate(User $user, Team $team): Response
    {
        return $user->can(PermissionKey::ORG_TEAMS_MANAGE_ANY)
            ? Response::allow()
            : Response::deny();
    }
}
