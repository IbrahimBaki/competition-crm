import { usePermissions } from '@/auth/usePermissions';
import { NAVIGATION, NavItem } from './navigation';

function isItemVisible(item: NavItem, can: (p: any) => boolean, canAny: (p: any) => boolean): boolean {
  if (item.permission && !can(item.permission)) {
    return false;
  }
  if (item.anyPermission && !canAny(item.anyPermission)) {
    return false;
  }
  return true;
}

function filterNavTree(items: readonly NavItem[], can: any, canAny: any): NavItem[] {
  return items
    .filter((item) => isItemVisible(item, can, canAny))
    .map((item) => ({
      ...item,
      children: item.children ? filterNavTree(item.children, can, canAny) : undefined,
    }))
    .filter((item) => !item.children || item.children.length > 0);
}

export function useVisibleNavigation(): NavItem[] {
  const { can, canAny } = usePermissions();
  return filterNavTree(NAVIGATION, can, canAny);
}
