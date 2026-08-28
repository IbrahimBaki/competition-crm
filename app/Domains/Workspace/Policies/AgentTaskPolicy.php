<?php

namespace App\Domains\Workspace\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Scoping\ResolveEffectiveScope;
use App\Domains\Workspace\Models\AgentTask;
use App\Models\User;

readonly class AgentTaskPolicy
{
    public function __construct(
        private ResolveEffectiveScope $scopeFilter,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionKey::WORKSPACE_TASKS_VIEW_OWN)
            || $user->can(PermissionKey::WORKSPACE_TASKS_VIEW_OTHERS);
    }

    public function view(User $user, AgentTask $task): bool
    {
        if ($user->id === $task->owner_id) {
            return true;
        }

        if ($user->can(PermissionKey::WORKSPACE_TASKS_VIEW_OTHERS)) {
            return $this->scopeFilter->allows($user, $task->owner->department);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionKey::WORKSPACE_TASKS_CREATE);
    }

    public function update(User $user, AgentTask $task): bool
    {
        return $user->id === $task->owner_id || $user->id === $task->created_by_id
            || $user->can(PermissionKey::WORKSPACE_TASKS_REASSIGN);
    }

    public function changeState(User $user, AgentTask $task): bool
    {
        return $this->update($user, $task);
    }

    public function reassign(User $user): bool
    {
        return $user->can(PermissionKey::WORKSPACE_TASKS_REASSIGN);
    }
}
