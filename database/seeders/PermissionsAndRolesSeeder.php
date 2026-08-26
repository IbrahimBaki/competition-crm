<?php

namespace Database\Seeders;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsAndRolesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdministrator();
        $this->seedManager();
        $this->seedSupervisor();
        $this->seedAgent();
        $this->seedViewer();
    }

    private function seedAdministrator(): void
    {
        $role = Role::firstOrCreate(
            ['name' => Role::ADMINISTRATOR],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مدير النظام',
                    'en' => 'Administrator',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, PermissionKey::all());
    }

    private function seedManager(): void
    {
        $permissions = [
            // admin.*
            PermissionKey::ADMIN_ROLES_MANAGE,
            PermissionKey::ADMIN_USERS_MANAGE,
            PermissionKey::ADMIN_USERS_INVITE,
            PermissionKey::ADMIN_USERS_ACTIVATE,
            PermissionKey::ADMIN_USERS_DEACTIVATE,
            PermissionKey::ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY,
            PermissionKey::ADMIN_STRUCTURE_MANAGE,
            // org.*
            PermissionKey::ORG_BRANCHES_VIEW_ANY,
            PermissionKey::ORG_BRANCHES_MANAGE_ANY,
            PermissionKey::ORG_DEPARTMENTS_VIEW_ANY,
            PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY,
            PermissionKey::ORG_TEAMS_VIEW_ANY,
            PermissionKey::ORG_TEAMS_MANAGE_ANY,
            // tickets.*
            PermissionKey::TICKETS_VIEW_ANY,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::MANAGER],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مدير',
                    'en' => 'Manager',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function seedSupervisor(): void
    {
        $permissions = [
            // org.*.view.any
            PermissionKey::ORG_BRANCHES_VIEW_ANY,
            PermissionKey::ORG_DEPARTMENTS_VIEW_ANY,
            PermissionKey::ORG_TEAMS_VIEW_ANY,
            // tickets.*
            PermissionKey::TICKETS_VIEW_DEPARTMENT,
            PermissionKey::TICKETS_VIEW_TEAM,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::SUPERVISOR],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مشرف',
                    'en' => 'Supervisor',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function seedAgent(): void
    {
        $permissions = [
            PermissionKey::TICKETS_VIEW_TEAM,
            PermissionKey::TICKETS_VIEW_OWN,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::AGENT],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'موظف دعم',
                    'en' => 'Support Agent',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function seedViewer(): void
    {
        $permissions = [
            PermissionKey::TICKETS_VIEW_OWN,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::VIEWER],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مطّلع',
                    'en' => 'Viewer',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function reconcilePermissions(Role $role, array $desiredPermissions): void
    {
        if (! $role->is_system) {
            return;
        }

        $existingKeys = $role->permissions()->pluck('permission_key')->toArray();

        $keysToAdd = array_diff($desiredPermissions, $existingKeys);
        $keysToRemove = array_diff($existingKeys, $desiredPermissions);

        if (! empty($keysToAdd)) {
            $permissions = array_map(fn ($key) => ['permission_key' => $key], $keysToAdd);
            $role->permissions()->createMany($permissions);
        }

        if (! empty($keysToRemove)) {
            $role->permissions()
                ->whereIn('permission_key', $keysToRemove)
                ->delete();
        }
    }
}
