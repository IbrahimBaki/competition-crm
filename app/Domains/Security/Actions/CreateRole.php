<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class CreateRole
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, array $data): Role
    {
        $keys = $data['permission_keys'] ?? [];

        $invalidKeys = array_diff($keys, PermissionKey::all());
        if (! empty($invalidKeys)) {
            throw new \InvalidArgumentException('Invalid permission keys: '.implode(', ', $invalidKeys));
        }

        $role = Role::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'is_system' => $data['is_system'] ?? false,
        ]);

        if (! empty($keys)) {
            $permissions = array_map(fn ($key) => ['permission_key' => $key], $keys);
            $role->permissions()->createMany($permissions);
        }

        $role->load('permissions');

        $this->auditLogger->record($actor, 'security.role.created', $role, null, $role->toArray());

        return $role;
    }
}
