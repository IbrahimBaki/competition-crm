<?php

namespace App\Domains\Integrations\Policies;

use App\Domains\Integrations\Models\ApiToken;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;

final class ApiTokenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionKey::INTEGRATIONS_API_TOKENS_MANAGE);
    }

    public function view(User $user, ApiToken $token): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionKey::INTEGRATIONS_API_TOKENS_MANAGE);
    }

    public function update(User $user, ApiToken $token): bool
    {
        return false;
    }

    public function delete(User $user, ApiToken $token): bool
    {
        return $user->hasPermission(PermissionKey::INTEGRATIONS_API_TOKENS_MANAGE);
    }
}
