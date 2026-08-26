<?php

namespace App\Domains\Security\Scoping;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Permissions\Scope;
use App\Models\User;

class ResolveEffectiveScope
{
    private const SCOPE_HIERARCHY = [
        'any' => 4,
        'department' => 3,
        'team' => 2,
        'own' => 1,
    ];

    public function resolve(User $user, string $module, string $action): ?Scope
    {
        $permissionKeys = $user->permissionKeys();
        $userScopes = [];

        foreach ($permissionKeys as $key) {
            $parsed = PermissionKey::parse($key);
            if ($parsed['module'] === $module && $parsed['action'] === $action && $parsed['scope'] !== null) {
                $userScopes[] = $parsed['scope'];
            }
        }

        if (empty($userScopes)) {
            return null;
        }

        $widestScope = max(array_map(
            fn ($scope) => self::SCOPE_HIERARCHY[$scope] ?? 0,
            $userScopes
        ));

        $scopeMap = array_flip(self::SCOPE_HIERARCHY);
        $scopeName = $scopeMap[$widestScope] ?? null;

        return $scopeName ? Scope::from($scopeName) : null;
    }
}
