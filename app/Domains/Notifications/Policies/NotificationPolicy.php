<?php

namespace App\Domains\Notifications\Policies;

use App\Domains\Notifications\Models\Notification;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::NOTIFICATIONS_VIEW_OWN)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, Notification $notification): Response
    {
        if ($notification->recipient_user_id !== $user->id) {
            return Response::deny();
        }

        return $user->can(PermissionKey::NOTIFICATIONS_VIEW_OWN)
            ? Response::allow()
            : Response::deny();
    }

    public function viewDeliveryLog(User $user): Response
    {
        return $user->can(PermissionKey::NOTIFICATIONS_VIEW_DELIVERY_LOG)
            ? Response::allow()
            : Response::deny();
    }
}
