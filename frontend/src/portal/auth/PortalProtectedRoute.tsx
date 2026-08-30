import { type ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { usePortalAuth } from './PortalAuthProvider';

export function PortalProtectedRoute({ children }: { children: ReactNode }) {
  const { isAuthenticated, loading } = usePortalAuth();

  if (loading) return <div className="ui-skeleton" role="status">Loading account…</div>;

  if (!isAuthenticated) {
    return <Navigate to="/portal/login" replace />;
  }

  return <>{children}</>;
}
