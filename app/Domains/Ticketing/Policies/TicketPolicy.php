<?php

namespace App\Domains\Ticketing\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Scoping\OrganisationStructureScopeFilter;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function __construct(
        private readonly OrganisationStructureScopeFilter $scopeFilter,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKETS_VIEW_OWN)
            || $user->hasPermissionTo(PermissionKey::TICKETS_VIEW_TEAM)
            || $user->hasPermissionTo(PermissionKey::TICKETS_VIEW_DEPARTMENT)
            || $user->hasPermissionTo(PermissionKey::TICKETS_VIEW_ANY);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermissionTo(PermissionKey::TICKETS_VIEW_ANY)) {
            return true;
        }

        if (! $this->scopeFilter->allows($user, $ticket->department)) {
            return false;
        }

        if ($user->hasPermissionTo(PermissionKey::TICKETS_VIEW_DEPARTMENT)) {
            return true;
        }

        if ($user->hasPermissionTo(PermissionKey::TICKETS_VIEW_TEAM) && $ticket->assignee?->id === $user->id) {
            return true;
        }

        if ($user->hasPermissionTo(PermissionKey::TICKETS_VIEW_OWN) && $ticket->created_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKETS_CREATE);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if (! $user->hasPermissionTo(PermissionKey::TICKETS_UPDATE)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        if (! $user->hasPermissionTo(PermissionKey::TICKETS_ASSIGN)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function reclassify(User $user, Ticket $ticket): bool
    {
        if (! $user->hasPermissionTo(PermissionKey::TICKETS_RECLASSIFY)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function tag(User $user, Ticket $ticket): bool
    {
        if (! $user->hasPermissionTo(PermissionKey::TICKETS_TAG)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function viewHistory(User $user, Ticket $ticket): bool
    {
        if (! $user->hasPermissionTo(PermissionKey::TICKETS_HISTORY_VIEW)) {
            return false;
        }

        return $this->view($user, $ticket);
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_STATUS_CHANGE)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_REOPEN)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function markSpam(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_SPAM_MARK)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function restoreFromSpam(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_SPAM_RESTORE)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function merge(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_MERGE)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function split(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_SPLIT)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }

    public function link(User $user, Ticket $ticket): bool
    {
        if ($ticket->isMerged()) {
            return false;
        }

        if (! $user->hasPermissionTo(PermissionKey::TICKETS_LINK)) {
            return false;
        }

        return $this->scopeFilter->allows($user, $ticket->department);
    }
}
