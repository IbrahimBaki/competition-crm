// Maps backend error envelope codes (app/Support/Http/Errors/ErrorCode.php)
// to i18n keys rendered inline on admin forms/dialogs. Codes and their exact
// string values were confirmed by reading ErrorCode.php and the exception
// classes that throw them directly — do not invent codes; the PHP constant
// NAME often differs from its string VALUE and from the plan's guesses
// (e.g. `AdminRoleLocked` = `'admin_role_locked'`, not
// `ADMINISTRATOR_ROLE_LOCKED`; `DepartmentInUseException` throws
// `department.has_open_tickets`, not a "department_in_use" code).
export const ADMIN_ERROR_MESSAGE_KEYS: Record<string, string> = {
  'branch.has_active_departments': 'admin.errors.branch_has_active_departments',
  'department.has_open_tickets': 'admin.errors.department_has_open_tickets',
  'admin_role_locked': 'admin.errors.admin_role_locked',
  'system_role_immutable': 'admin.errors.system_role_immutable',
  'cannot_deactivate_self': 'admin.errors.cannot_deactivate_self',
  'cannot_deactivate_last_administrator': 'admin.errors.cannot_deactivate_last_administrator',
  'sla.policy_in_use': 'admin.errors.sla_policy_in_use',
  'ticket.category_inactive': 'admin.errors.ticket_category_inactive',
  'ticket.category_depth_exceeded': 'admin.errors.ticket_category_depth_exceeded',
  'automation.execution_immutable': 'admin.errors.automation_execution_immutable',
  'automation.rule_key_taken': 'admin.errors.automation_rule_key_taken',
  'email.inbound_already_processed': 'admin.errors.email_inbound_already_processed',
  'user.branch_not_attached': 'admin.errors.user_branch_not_attached',
};

export function adminErrorMessageKey(code: string | undefined | null): string {
  return (code && ADMIN_ERROR_MESSAGE_KEYS[code]) ?? 'admin.errors.unexpected';
}
