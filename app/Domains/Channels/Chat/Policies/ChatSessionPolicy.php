<?php

namespace App\Domains\Channels\Chat\Policies;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;

class ChatSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionKey::CHANNELS_CHAT_VIEW);
    }

    public function view(User $user, ChatSession $session): bool
    {
        return $user->can(PermissionKey::CHANNELS_CHAT_VIEW) && $this->inScope($user, $session);
    }

    public function accept(User $user, ChatSession $session): bool
    {
        return $user->can(PermissionKey::CHANNELS_CHAT_ACCEPT) && $this->inScope($user, $session);
    }

    /**
     * Sending a reply requires being the agent the session is assigned to —
     * view alone would let any agent in the department post into a chat they
     * were never handed.
     */
    public function sendMessage(User $user, ChatSession $session): bool
    {
        if (! $user->can(PermissionKey::CHANNELS_CHAT_VIEW)) {
            return false;
        }

        return $session->assigned_user_id === $user->uuid
            || $user->can(PermissionKey::CHANNELS_CHAT_MANAGE);
    }

    public function transfer(User $user, ChatSession $session): bool
    {
        if (! $user->can(PermissionKey::CHANNELS_CHAT_TRANSFER)) {
            return false;
        }

        return $session->assigned_user_id === $user->uuid
            || $user->can(PermissionKey::CHANNELS_CHAT_MANAGE);
    }

    public function end(User $user, ChatSession $session): bool
    {
        return $user->can(PermissionKey::CHANNELS_CHAT_MANAGE);
    }

    /**
     * Structural reachability: a chat session belongs to one department, so
     * reuse the same "is this within the user's part of the org" rule the
     * ticket queue applies — a manage-level agent can always reach it.
     */
    private function inScope(User $user, ChatSession $session): bool
    {
        if ($user->can(PermissionKey::CHANNELS_CHAT_MANAGE)) {
            return true;
        }

        return $session->department_id !== null
            && $user->departments()->whereKey($session->department_id)->exists();
    }
}
