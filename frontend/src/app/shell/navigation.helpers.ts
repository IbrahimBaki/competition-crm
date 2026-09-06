import type { NavItem } from '@/shell/navigation';

/** Segment-boundary-aware "is this route (or a route beneath it) current" check. */
export function isNavPathActive(pathname: string, path: string): boolean {
  return pathname === path || pathname.startsWith(`${path}/`);
}

// router.tsx renders the same WorkspacePage for both the index route ("/")
// and the literal "/workspace" path — the Workspace nav item must read as
// current on either.
export function isWorkspaceActive(pathname: string): boolean {
  return pathname === '/' || pathname === '/workspace';
}

export function isNavItemActive(pathname: string, item: Pick<NavItem, 'path'>): boolean {
  return item.path === '/' ? isWorkspaceActive(pathname) : isNavPathActive(pathname, item.path);
}

export function hasActiveDescendant(pathname: string, children: readonly Pick<NavItem, 'path'>[]): boolean {
  return children.some((child) => isNavItemActive(pathname, child));
}
