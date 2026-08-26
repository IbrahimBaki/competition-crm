<?php

return [
    'bilingual' => [
        'invalid_payload' => 'Bilingual field must be an array with ar and en keys.',
    ],
    'organisation' => [
        'branch_has_active_departments' => 'Branch has :count active departments',
        'department_has_open_tickets' => 'Department has :count open tickets',
        'user_branch_not_attached' => 'User is not attached to this branch',
    ],
    'security' => [
        'account_deactivated' => 'Account has been deactivated',
        'account_locked' => 'Account is locked',
        'administrator_role_locked' => 'Cannot modify administrators',
        'cannot_deactivate_last_administrator' => 'Cannot deactivate the last administrator',
        'cannot_deactivate_self' => 'Cannot deactivate your own account',
        'invalid_two_factor_code' => 'Invalid two-factor code',
        'invitation_already_accepted' => 'Invitation has already been accepted',
        'invitation_already_pending' => 'An invitation is already pending for :email',
        'invitation_expired' => 'Invitation has expired',
        'system_role_immutable' => 'System roles cannot be modified',
        'two_factor_already_enabled' => 'Two-factor authentication is already enabled',
        'two_factor_required' => 'Two-factor authentication is required',
        'user_already_deactivated' => 'User is already deactivated',
    ],
];
