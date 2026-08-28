<?php

namespace App\Domains\Workspace\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Workspace\Models\QuickReply;
use App\Domains\Workspace\Models\QuickReplyScope;
use App\Models\User;

readonly class QuickReplyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, QuickReply $reply): bool
    {
        if ($reply->scope === QuickReplyScope::Personal) {
            return $user->id === $reply->owner_id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, QuickReply $reply): bool
    {
        if ($reply->scope === QuickReplyScope::Personal) {
            return $user->id === $reply->owner_id;
        }

        return $user->can(PermissionKey::WORKSPACE_QUICK_REPLIES_MANAGE_SHARED);
    }

    public function delete(User $user, QuickReply $reply): bool
    {
        return $this->update($user, $reply);
    }
}
