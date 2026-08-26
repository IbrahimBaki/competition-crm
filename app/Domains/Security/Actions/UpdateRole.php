<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\AdministratorRoleLockedException;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;

class UpdateRole
{
    public function execute(Role $role, array $data): Role
    {
        $keys = $data['permission_keys'] ?? [];

        $invalidKeys = array_diff($keys, PermissionKey::all());
        if (! empty($invalidKeys)) {
            throw new \InvalidArgumentException('Invalid permission keys: '.implode(', ', $invalidKeys));
        }

        if ($role->name === Role::ADMINISTRATOR) {
            $adminKeys = array_filter(PermissionKey::all(), fn ($key) => str_starts_with($key, 'admin.'));
            $missingAdminKeys = array_diff($adminKeys, $keys);
            if (! empty($missingAdminKeys)) {
                throw new AdministratorRoleLockedException('Cannot remove administrative permissions from the administrator role.');
            }
        }

        $role->update([
            'display_name' => $data['display_name'] ?? $role->display_name,
        ]);

        $role->permissions()->delete();
        if (! empty($keys)) {
            $permissions = array_map(fn ($key) => ['permission_key' => $key], $keys);
            $role->permissions()->createMany($permissions);
        }

        return $role->fresh('permissions');
    }
}
