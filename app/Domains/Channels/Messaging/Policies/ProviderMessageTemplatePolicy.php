<?php

namespace App\Domains\Channels\Messaging\Policies;

use App\Domains\Channels\Messaging\Models\ProviderMessageTemplate;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;

class ProviderMessageTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionKey::CHANNELS_MESSAGING_TEMPLATES_VIEW);
    }

    public function view(User $user, ProviderMessageTemplate $template): bool
    {
        return $user->can(PermissionKey::CHANNELS_MESSAGING_TEMPLATES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionKey::CHANNELS_MESSAGING_TEMPLATES_MANAGE);
    }

    public function update(User $user, ProviderMessageTemplate $template): bool
    {
        return $user->can(PermissionKey::CHANNELS_MESSAGING_TEMPLATES_MANAGE);
    }

    public function delete(User $user, ProviderMessageTemplate $template): bool
    {
        return $user->can(PermissionKey::CHANNELS_MESSAGING_TEMPLATES_MANAGE);
    }
}
