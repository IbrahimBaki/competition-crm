<?php

namespace App\Domains\Channels\WebForm\Policies;

use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;

class WebFormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionKey::CHANNELS_WEB_FORM_VIEW);
    }

    public function view(User $user, WebForm $form): bool
    {
        return $user->can(PermissionKey::CHANNELS_WEB_FORM_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionKey::CHANNELS_WEB_FORM_CREATE);
    }

    public function update(User $user, WebForm $form): bool
    {
        return $user->can(PermissionKey::CHANNELS_WEB_FORM_UPDATE);
    }

    public function delete(User $user, WebForm $form): bool
    {
        return $user->can(PermissionKey::CHANNELS_WEB_FORM_DELETE);
    }
}
