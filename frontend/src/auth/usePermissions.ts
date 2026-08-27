import { useAuth } from './AuthProvider';
import { hasPermission, hasAnyPermission, hasAllPermissions, PermissionKey } from './permissions';

export function usePermissions() {
  const { permissions } = useAuth();

  return {
    can: (permission: PermissionKey) => hasPermission(permissions, permission),
    canAny: (permissions: readonly PermissionKey[]) =>
      hasAnyPermission(permissions, permissions),
    canAll: (required: readonly PermissionKey[]) =>
      hasAllPermissions(permissions, required),
  };
}
