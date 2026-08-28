// Permission keys copied verbatim from app/Domains/Security/Permissions/PermissionKey.php
// Only keys needed for this story are included; full catalogue is a later concern.
export const PERMISSIONS = {
  // Admin permissions
  ADMIN_ROLES_MANAGE: 'admin.roles.manage',
  ADMIN_USERS_MANAGE: 'admin.users.manage',
  ADMIN_USERS_INVITE: 'admin.users.invite',

  // Tickets permissions
  TICKETS_VIEW_ANY: 'tickets.view.any',
  TICKETS_VIEW_OWN: 'tickets.view.own',
  TICKETS_VIEW_DEPARTMENT: 'tickets.view.department',
  TICKETS_VIEW_TEAM: 'tickets.view.team',
  TICKETS_CREATE: 'tickets.create',
  TICKETS_UPDATE: 'tickets.update',
  TICKETS_ASSIGN: 'tickets.assign',
  TICKETS_CLAIM: 'tickets.claim',
  TICKETS_TRANSFER_AGENT: 'tickets.transfer.agent',
  TICKETS_TRANSFER_DEPARTMENT: 'tickets.transfer.department',
  TICKETS_QUEUE_VIEW: 'tickets.queue.view',
  TICKETS_RECLASSIFY: 'tickets.reclassify',
  TICKETS_TAG: 'tickets.tag',
  TICKETS_HISTORY_VIEW: 'tickets.history.view',
  TICKETS_STATUS_CHANGE: 'tickets.status.change',
  TICKETS_REOPEN: 'tickets.reopen',
  TICKETS_SPAM_MARK: 'tickets.spam.mark',
  TICKETS_SPAM_RESTORE: 'tickets.spam.restore',
  TICKETS_MERGE: 'tickets.merge',
  TICKETS_SPLIT: 'tickets.split',
  TICKETS_LINK: 'tickets.link',
  TICKET_MESSAGE_VIEW: 'ticket.message.view',
  TICKET_MESSAGE_SEND: 'ticket.message.send',
  TICKET_MESSAGE_INTERNAL_VIEW: 'ticket.message.internal_view',
  TICKET_MESSAGE_INTERNAL_WRITE: 'ticket.message.internal_write',
  TICKET_MESSAGE_RETRY: 'ticket.message.retry',
  TICKETS_ESCALATE: 'tickets.escalate',
  WORKSPACE_TICKET_WATCHERS_VIEW: 'workspace.ticket.watchers.view',
  WORKSPACE_TICKET_MESSAGE_MENTION: 'workspace.ticket.message.mention',

  // Attachments
  ATTACHMENTS_UPLOAD: 'attachments.upload',

  // Customers
  CUSTOMERS_VIEW: 'customers.view',
  CUSTOMERS_TIMELINE_VIEW: 'customers.timeline.view',

  // Organisation permissions
  ORG_BRANCHES_VIEW_ANY: 'org.branches.view.any',
  ORG_DEPARTMENTS_VIEW_ANY: 'org.departments.view.any',
  ORG_TEAMS_VIEW_ANY: 'org.teams.view.any',
} as const;

export type PermissionKey = (typeof PERMISSIONS)[keyof typeof PERMISSIONS];

export function hasPermission(
  granted: readonly string[],
  required: PermissionKey
): boolean {
  return granted.includes(required);
}

export function hasAnyPermission(
  granted: readonly string[],
  required: readonly PermissionKey[]
): boolean {
  return required.some((key) => granted.includes(key));
}

export function hasAllPermissions(
  granted: readonly string[],
  required: readonly PermissionKey[]
): boolean {
  return required.every((key) => granted.includes(key));
}
