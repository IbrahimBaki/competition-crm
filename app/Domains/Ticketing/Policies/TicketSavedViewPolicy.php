<?php

namespace App\Domains\Ticketing\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Ticketing\Models\TicketSavedView;
use App\Models\User;

class TicketSavedViewPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyTicketViewScope($user);
    }

    public function view(User $user, TicketSavedView $view): bool
    {
        return $this->hasAnyTicketViewScope($user)
            && ($view->is_shared || $view->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyTicketViewScope($user);
    }

    public function update(User $user, TicketSavedView $view): bool
    {
        return $view->user_id === $user->id;
    }

    public function delete(User $user, TicketSavedView $view): bool
    {
        return $view->user_id === $user->id;
    }

    private function hasAnyTicketViewScope(User $user): bool
    {
        return $user->can(PermissionKey::TICKETS_VIEW_OWN)
            || $user->can(PermissionKey::TICKETS_VIEW_TEAM)
            || $user->can(PermissionKey::TICKETS_VIEW_DEPARTMENT)
            || $user->can(PermissionKey::TICKETS_VIEW_ANY);
    }
}
