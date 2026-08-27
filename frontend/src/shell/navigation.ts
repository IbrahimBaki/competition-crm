import { PermissionKey } from '@/auth/permissions';

export interface NavItem {
  id: string;
  labelKey: string;
  path: string;
  icon?: string;
  permission?: PermissionKey;
  anyPermission?: readonly PermissionKey[];
  children?: readonly NavItem[];
}

export const NAVIGATION: readonly NavItem[] = [
  {
    id: 'dashboard',
    labelKey: 'nav.dashboard',
    path: '/',
    icon: '📊',
  },
  {
    id: 'ticketing',
    labelKey: 'nav.ticketing',
    path: '/tickets',
    icon: '🎫',
    permission: 'tickets.view.any',
  },
  {
    id: 'admin',
    labelKey: 'nav.admin',
    icon: '⚙️',
    path: '#',
    permission: 'admin.roles.manage',
    children: [
      {
        id: 'admin-users',
        labelKey: 'nav.admin.users',
        path: '/admin/users',
        permission: 'admin.users.manage',
      },
      {
        id: 'admin-roles',
        labelKey: 'nav.admin.roles',
        path: '/admin/roles',
        permission: 'admin.roles.manage',
      },
    ],
  },
];
