import React from 'react';
import { usePermissions } from './usePermissions';
import { PermissionKey } from './permissions';
import { ForbiddenState } from '@/shell/states/ForbiddenState';

interface RequirePermissionProps {
  permission?: PermissionKey;
  anyPermission?: readonly PermissionKey[];
  allPermissions?: readonly PermissionKey[];
  fallback?: React.ReactNode;
  children: React.ReactNode;
}

export function RequirePermission({
  permission,
  anyPermission,
  allPermissions,
  fallback = <ForbiddenState />,
  children,
}: RequirePermissionProps) {
  const { can, canAny, canAll } = usePermissions();

  let hasAccess = true;

  if (permission) {
    hasAccess = can(permission);
  } else if (anyPermission) {
    hasAccess = canAny(anyPermission);
  } else if (allPermissions) {
    hasAccess = canAll(allPermissions);
  }

  return hasAccess ? <>{children}</> : <>{fallback}</>;
}
