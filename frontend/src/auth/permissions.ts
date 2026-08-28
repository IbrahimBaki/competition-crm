// Permission keys copied verbatim from app/Domains/Security/Permissions/PermissionKey.php
// Only keys needed for this story are included; full catalogue is a later concern.
export const PERMISSIONS = {
  // Admin permissions
  ADMIN_ROLES_MANAGE: 'admin.roles.manage',
  ADMIN_USERS_MANAGE: 'admin.users.manage',
  ADMIN_USERS_INVITE: 'admin.users.invite',
  ADMIN_USERS_ACTIVATE: 'admin.users.activate',
  ADMIN_USERS_DEACTIVATE: 'admin.users.deactivate',
  ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY: 'admin.users.manage_two_factor_policy',
  ADMIN_STRUCTURE_MANAGE: 'admin.structure.manage',
  ADMIN_AUDIT_VIEW: 'admin.audit.view',

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

  // Workspace: agent tasks & quick replies
  WORKSPACE_TASKS_CREATE: 'workspace.tasks.create',
  WORKSPACE_TASKS_VIEW_OWN: 'workspace.tasks.view.own',
  WORKSPACE_TASKS_VIEW_OTHERS: 'workspace.tasks.view.others',
  WORKSPACE_TASKS_REASSIGN: 'workspace.tasks.reassign',
  WORKSPACE_QUICK_REPLIES_MANAGE_SHARED: 'workspace.quick_replies.manage.shared',

  // Notifications
  NOTIFICATIONS_VIEW_OWN: 'notifications.view.own',
  NOTIFICATIONS_MANAGE_PREFERENCES: 'notifications.manage_preferences',
  NOTIFICATIONS_VIEW_DELIVERY_LOG: 'notifications.view_delivery_log',

  // Attachments
  ATTACHMENTS_UPLOAD: 'attachments.upload',

  // Customers
  CUSTOMERS_VIEW: 'customers.view',
  CUSTOMERS_CREATE: 'customers.create',
  CUSTOMERS_UPDATE: 'customers.update',
  CUSTOMERS_BLOCK: 'customers.block',
  CUSTOMERS_CONTACT_MANAGE: 'customers.contact.manage',
  CUSTOMERS_NOTE_VIEW: 'customers.note.view',
  CUSTOMERS_NOTE_CREATE: 'customers.note.create',
  CUSTOMERS_NOTE_DELETE: 'customers.note.delete',
  CUSTOMERS_ATTACHMENT_MANAGE: 'customers.attachment.manage',
  CUSTOMERS_TIMELINE_VIEW: 'customers.timeline.view',
  CUSTOMERS_DUPLICATE_VIEW: 'customers.duplicate.view',
  CUSTOMERS_DUPLICATE_REVIEW: 'customers.duplicate.review',
  CUSTOMERS_MERGE: 'customers.merge',

  // Organisation permissions
  ORG_BRANCHES_VIEW_ANY: 'org.branches.view.any',
  ORG_BRANCHES_MANAGE_ANY: 'org.branches.manage.any',
  ORG_DEPARTMENTS_VIEW_ANY: 'org.departments.view.any',
  ORG_DEPARTMENTS_MANAGE_ANY: 'org.departments.manage.any',
  ORG_TEAMS_VIEW_ANY: 'org.teams.view.any',
  ORG_TEAMS_MANAGE_ANY: 'org.teams.manage.any',

  // Ticket catalogue (statuses/categories) — admin
  TICKETS_CATEGORIES_MANAGE: 'tickets.categories.manage',
  TICKETS_STATUSES_MANAGE: 'tickets.statuses.manage',

  // SLA
  SLA_POLICIES_VIEW: 'sla.policies.view',
  SLA_POLICIES_MANAGE: 'sla.policies.manage',
  SLA_RESET: 'sla.reset',

  // Automation
  AUTOMATION_RULES_VIEW: 'automation.rules.view',
  AUTOMATION_RULES_MANAGE: 'automation.rules.manage',
  AUTOMATION_EXECUTIONS_VIEW: 'automation.executions.view',

  // Channels — email replay
  CHANNELS_EMAIL_REPLAY_LIST: 'channels.email.replay.list',
  CHANNELS_EMAIL_REPLAY_ACTION: 'channels.email.replay.action',

  // Channels — web forms
  CHANNELS_WEB_FORM_VIEW: 'channels.web_form.view',
  CHANNELS_WEB_FORM_CREATE: 'channels.web_form.create',
  CHANNELS_WEB_FORM_UPDATE: 'channels.web_form.update',
  CHANNELS_WEB_FORM_DELETE: 'channels.web_form.delete',

  // Channels — messaging templates
  CHANNELS_MESSAGING_TEMPLATES_VIEW: 'channels.messaging.templates.view',
  CHANNELS_MESSAGING_TEMPLATES_MANAGE: 'channels.messaging.templates.manage',

  // Reports
  REPORTS_VIEW_OWN: 'reports.view.own',
  REPORTS_VIEW_DEPARTMENT: 'reports.view.department',
  REPORTS_VIEW_BRANCH: 'reports.view.branch',
  REPORTS_VIEW_ANY: 'reports.view.any',
  REPORTS_EXPORT_ANY: 'reports.export.any',
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
