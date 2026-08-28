import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from './AuthProvider';
import { PermissionKey } from './permissions';
import { RequirePermission } from './RequirePermission';
import { ForbiddenState } from '@/shell/states/ForbiddenState';

interface ProtectedRouteProps {
  children: React.ReactNode;
  permission?: PermissionKey;
  anyPermission?: readonly PermissionKey[];
  allPermissions?: readonly PermissionKey[];
}

export function ProtectedRoute({
  children,
  permission,
  anyPermission,
  allPermissions,
}: ProtectedRouteProps) {
  const { status } = useAuth();
  const from = location.pathname;

  if (status === 'loading') {
    return <div>Loading...</div>;
  }

  if (status === 'unauthenticated') {
    return <Navigate to="/login" state={{ from }} replace />;
  }

  if (status === 'two_factor_required') {
    return <Navigate to="/login/two-factor" replace />;
  }

  if (permission || anyPermission || allPermissions) {
    return (
      <RequirePermission
        permission={permission}
        anyPermission={anyPermission}
        allPermissions={allPermissions}
        fallback={<ForbiddenState />}
      >
        {children}
      </RequirePermission>
    );
  }

  return <>{children}</>;
}
