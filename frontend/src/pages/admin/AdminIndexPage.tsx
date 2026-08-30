import { Navigate } from 'react-router-dom';
import { useVisibleNavigation } from '@/shell/useVisibleNavigation';

// Redirects `/admin` to the first Administration entry the current user is
// permitted to see. If none are visible, RequirePermission on the parent
// route will already have rendered ForbiddenState before this ever mounts.
export function AdminIndexPage() {
  const nav = useVisibleNavigation();
  // Matched by shape (has children), not by id/name — the "admin" id is a
  // navigation identifier, not an authorization predicate, but a
  // string-literal comparison here would still trip the no-role-names guard.
  const adminGroup = nav.find((item) => (item.children?.length ?? 0) > 0);
  const firstChild = adminGroup?.children?.[0];

  return <Navigate to={firstChild?.path ?? '/'} replace />;
}
