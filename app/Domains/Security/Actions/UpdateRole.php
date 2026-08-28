<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\AdministratorRoleLockedException;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class UpdateRole
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, Role $role, array $data): Role
    {
        $before = $role->toArray();

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

        $role = $role->fresh('permissions');

        $this->auditLogger->record($actor, 'security.role.updated', $role, $before, $role->toArray());

        return $role;
    }
}
