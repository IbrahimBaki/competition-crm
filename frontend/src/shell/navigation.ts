import { PermissionKey, TICKETS_VIEW_SCOPES, REPORTS_VIEW_SCOPES } from '@/auth/permissions';

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
    id: 'workspace',
    labelKey: 'nav.workspace',
    path: '/',
    icon: 'workspace',
  },
  {
    id: 'ticketing',
    labelKey: 'nav.ticketing',
    path: '/tickets',
    icon: 'tickets',
    anyPermission: TICKETS_VIEW_SCOPES,
  },
  {
    id: 'customers',
    labelKey: 'nav.customers',
    path: '/customers',
    icon: 'customers',
    permission: 'customers.view',
  },
  {
    id: 'knowledge',
    labelKey: 'nav.knowledge',
    path: '/knowledge',
    icon: 'knowledge',
    permission: 'knowledge.articles.view',
  },
  {
    id: 'reports',
    labelKey: 'reports.navigation.reports',
    path: '/reports',
    icon: 'reports',
    anyPermission: REPORTS_VIEW_SCOPES,
  },
  {
    id: 'dashboard',
    labelKey: 'reports.navigation.dashboard',
    path: '/dashboard',
    icon: 'dashboard',
    anyPermission: REPORTS_VIEW_SCOPES,
  },
  {
    // No `permission`/`anyPermission` on the group itself: filterNavTree
    // drops a parent whose children are all filtered out, and keeps it
    // otherwise — that's the correct "show if any admin area is visible"
    // behaviour. Gating the group itself on one specific key (as the
    // pre-existing placeholder did with `admin.roles.manage`) would hide
    // the whole group from an administrator who can only manage branches.
    id: 'admin',
    labelKey: 'nav.admin',
    icon: 'admin',
    path: '#',
    children: [
      {
        id: 'admin-branches',
        labelKey: 'nav.admin.branches',
        path: '/admin/branches',
        permission: 'org.branches.view.any',
      },
      {
        id: 'admin-departments',
        labelKey: 'nav.admin.departments',
        path: '/admin/departments',
        permission: 'org.departments.view.any',
      },
      {
        id: 'admin-teams',
        labelKey: 'nav.admin.teams',
        path: '/admin/teams',
        permission: 'org.teams.view.any',
      },
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
      {
        id: 'admin-ticket-catalogue',
        labelKey: 'nav.admin.ticket_catalogue',
        path: '/admin/ticket-catalogue',
        anyPermission: ['tickets.statuses.manage', 'tickets.categories.manage'],
      },
      {
        id: 'admin-sla-policies',
        labelKey: 'nav.admin.sla_policies',
        path: '/admin/sla-policies',
        permission: 'sla.policies.view',
      },
      {
        id: 'admin-automation-rules',
        labelKey: 'nav.admin.automation_rules',
        path: '/admin/automation-rules',
        permission: 'automation.rules.view',
      },
      {
        id: 'admin-channels',
        labelKey: 'nav.admin.channels',
        path: '/admin/channels',
        anyPermission: ['channels.email.replay.list', 'channels.web_form.view', 'channels.messaging.templates.view', 'channels.chat.view'],
      },
      {
        id: 'admin-settings',
        labelKey: 'nav.admin.settings',
        path: '/admin/settings',
        permission: 'admin.users.manage_two_factor_policy',
      },
      {
        id: 'admin-audit',
        labelKey: 'nav.admin.audit',
        path: '/admin/audit',
        permission: 'admin.audit.view',
      },
      {
        id: 'admin-data-protection',
        labelKey: 'nav.admin.data_protection',
        path: '/admin/data-protection',
        permission: 'dataprotection.retention.view',
      },
      {
        id: 'admin-integrations',
        labelKey: 'nav.admin.integrations',
        path: '/admin/integrations',
        anyPermission: ['integrations.api_tokens.manage', 'integrations.webhooks.manage', 'integrations.import.manage'],
      },
      {
        id: 'admin-ai',
        labelKey: 'nav.admin.ai',
        path: '/admin/ai',
        anyPermission: ['ai.usage.view', 'ai.settings.manage'],
      },
    ],
  },
];
