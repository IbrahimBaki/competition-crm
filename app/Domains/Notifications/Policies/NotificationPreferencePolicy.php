<?php

namespace App\Domains\Notifications\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPreferencePolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::NOTIFICATIONS_MANAGE_PREFERENCES)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user): Response
    {
        return $user->can(PermissionKey::NOTIFICATIONS_MANAGE_PREFERENCES)
            ? Response::allow()
            : Response::deny();
    }
}
