// Permission keys copied verbatim from app/Domains/Security/Permissions/PermissionKey.php
// Only keys needed for this story are included; full catalogue is a later concern.
export const PERMISSIONS = {
  // Admin permissions
  ADMIN_ROLES_MANAGE: 'admin.roles.manage',
  ADMIN_USERS_MANAGE: 'admin.users.manage',
  ADMIN_USERS_INVITE: 'admin.users.invite',

  // Tickets permissions (for the gated stub route)
  TICKETS_VIEW_ANY: 'tickets.view.any',
  TICKETS_VIEW_OWN: 'tickets.view.own',
  TICKETS_VIEW_DEPARTMENT: 'tickets.view.department',
  TICKETS_VIEW_TEAM: 'tickets.view.team',
  TICKETS_CREATE: 'tickets.create',
  TICKETS_UPDATE: 'tickets.update',

  // Organisation permissions
  ORG_BRANCHES_VIEW_ANY: 'org.branches.view.any',
  ORG_DEPARTMENTS_VIEW_ANY: 'org.departments.view.any',
  ORG_TEAMS_VIEW_ANY: 'org.teams.view.any',
} as const;

export type PermissionKey = (typeof PERMISSIONS)[keyof typeof PERMISSIONS];

export function hasPermission(
  granted: readonly string[],
  required: PermissionKey
): boolean {
  return granted.includes(required);
}

export function hasAnyPermission(
  granted: readonly string[],
  required: readonly PermissionKey[]
): boolean {
  return required.some((key) => granted.includes(key));
}

export function hasAllPermissions(
  granted: readonly string[],
  required: readonly PermissionKey[]
): boolean {
  return required.every((key) => granted.includes(key));
}
