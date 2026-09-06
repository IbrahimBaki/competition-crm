import { useLocation } from 'react-router-dom';
import { NAVIGATION } from '@/shell/navigation';
import { isNavItemActive } from './navigation.helpers';

export interface PageContext {
  labelKey: string;
  parentLabelKey?: string;
  parentPath?: string;
}

/**
 * Derives a masthead page-context label (and, for admin sub-routes, a two-
 * level breadcrumb) purely from static nav metadata — never from fetched
 * record data, so it stays honest for detail routes like `/tickets/:id`
 * where only the parent "Tickets" label is safely derivable.
 */
export function usePageContext(): PageContext | null {
  const { pathname } = useLocation();

  for (const item of NAVIGATION) {
    if (item.children) {
      const child = item.children.find((candidate) => isNavItemActive(pathname, candidate));
      if (child) {
        // `item.path` is the literal '#' placeholder NAVIGATION uses for a
        // disclosure group with no page of its own (see shell/navigation.ts).
        // '/admin' is still a real route (AdminIndexPage, router.tsx) that
        // every admin child sits under, so the breadcrumb can safely link
        // there without inventing a destination NAVIGATION doesn't have.
        return { labelKey: child.labelKey, parentLabelKey: item.labelKey, parentPath: '/admin' };
      }
      continue;
    }
    if (isNavItemActive(pathname, item)) return { labelKey: item.labelKey };
  }

  if (pathname.startsWith('/admin')) return { labelKey: 'nav.admin' };

  return null;
}
