<?php

namespace App\Domains\Ticketing\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Models\User;

class TicketCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TicketCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionKey::TICKETS_CATEGORIES_MANAGE);
    }

    public function update(User $user, TicketCategory $category): bool
    {
        return $user->can(PermissionKey::TICKETS_CATEGORIES_MANAGE);
    }

    public function delete(User $user, TicketCategory $category): bool
    {
        return $user->can(PermissionKey::TICKETS_CATEGORIES_MANAGE);
    }
}
