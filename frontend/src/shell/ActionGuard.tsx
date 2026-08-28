import React from 'react';
import { usePermissions } from '@/auth/usePermissions';
import { PermissionKey } from '@/auth/permissions';

interface ActionGuardProps {
  permission?: PermissionKey;
  anyPermission?: readonly PermissionKey[];
  allPermissions?: readonly PermissionKey[];
  children: React.ReactNode;
}

export function ActionGuard({
  permission,
  anyPermission,
  allPermissions,
  children,
}: ActionGuardProps) {
  const { can, canAny, canAll } = usePermissions();

  let hasAccess = true;

  if (permission) {
    hasAccess = can(permission);
  } else if (anyPermission) {
    hasAccess = canAny(anyPermission);
  } else if (allPermissions) {
    hasAccess = canAll(allPermissions);
  }

  return hasAccess ? <>{children}</> : null;
}
