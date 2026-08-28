<?php

namespace App\Domains\Ticketing\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Models\User;

class TicketStatusDefinitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionKey::TICKETS_VIEW_OWN)
            || $user->can(PermissionKey::TICKETS_VIEW_TEAM)
            || $user->can(PermissionKey::TICKETS_VIEW_DEPARTMENT)
            || $user->can(PermissionKey::TICKETS_VIEW_ANY);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionKey::TICKETS_STATUSES_MANAGE);
    }

    public function update(User $user, TicketStatusDefinition $status): bool
    {
        if ($status->is_system) {
            return $user->can(PermissionKey::TICKETS_STATUSES_MANAGE);
        }

        return $user->can(PermissionKey::TICKETS_STATUSES_MANAGE);
    }

    public function delete(User $user, TicketStatusDefinition $status): bool
    {
        if ($status->is_system) {
            return false;
        }

        return $user->can(PermissionKey::TICKETS_STATUSES_MANAGE);
    }
}
