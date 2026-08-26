<?php

namespace App\Domains\Security\Permissions;

use ReflectionClass;

final class PermissionKey
{
    // ---- admin (module: admin) ------------------------------------------
    public const ADMIN_ROLES_MANAGE = 'admin.roles.manage';

    public const ADMIN_USERS_MANAGE = 'admin.users.manage';

    public const ADMIN_STRUCTURE_MANAGE = 'admin.structure.manage';

    // ---- organisation (module: org) ------------------------------------
    public const ORG_BRANCHES_VIEW_ANY = 'org.branches.view.any';

    public const ORG_BRANCHES_MANAGE_ANY = 'org.branches.manage.any';

    public const ORG_DEPARTMENTS_VIEW_ANY = 'org.departments.view.any';

    public const ORG_DEPARTMENTS_MANAGE_ANY = 'org.departments.manage.any';

    public const ORG_TEAMS_VIEW_ANY = 'org.teams.view.any';

    public const ORG_TEAMS_MANAGE_ANY = 'org.teams.manage.any';

    // ---- tickets (module: tickets) - scope-aware -----------------------
    public const TICKETS_VIEW_OWN = 'tickets.view.own';

    public const TICKETS_VIEW_TEAM = 'tickets.view.team';

    public const TICKETS_VIEW_DEPARTMENT = 'tickets.view.department';

    public const TICKETS_VIEW_ANY = 'tickets.view.any';

    public static function all(): array
    {
        $reflection = new ReflectionClass(self::class);
        $keys = [];
        foreach ($reflection->getConstants() as $const) {
            if (is_string($const)) {
                $keys[] = $const;
            }
        }

        return $keys;
    }

    /**
     * @return array{module: string, action: string, scope: ?string}
     */
    public static function parse(string $key): array
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) {
            throw new \InvalidArgumentException("Invalid permission key format: {$key}");
        }

        $module = array_shift($parts);
        $lastPart = end($parts);

        try {
            $scope = Scope::from($lastPart);
            array_pop($parts);
            $action = implode('.', $parts);
        } catch (\ValueError) {
            $scope = null;
            $action = implode('.', $parts);
        }

        return [
            'module' => $module,
            'action' => $action,
            'scope' => $scope?->value,
        ];
    }
}
