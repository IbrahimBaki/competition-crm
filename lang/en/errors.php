<?php

return [
    'attachments' => [
        'scan_pending' => 'File is being scanned and is not yet available',
        'scan_failed' => 'File failed the malware scan and was rejected',
        'too_large' => 'File exceeds the maximum allowed size of :max KB',
        'type_not_allowed' => 'File type is not allowed',
    ],
    'bilingual' => [
        'invalid_payload' => 'Bilingual field must be an array with ar and en keys.',
    ],
    'organisation' => [
        'branch_has_active_departments' => 'Branch has :count active departments',
        'department_has_open_tickets' => 'Department has :count open tickets',
        'user_branch_not_attached' => 'User is not attached to this branch',
    ],
    'customers' => [
        'blocked' => 'Customer is blocked: :reason',
        'already_blocked' => 'Customer is already blocked',
        'not_blocked' => 'Customer is not blocked',
        'duplicate_contact_identity' => 'This contact identity is already in use',
        'must_have_contact' => 'Customer must have at least one contact',
    ],
    'security' => [
        'account_deactivated' => 'Account has been deactivated',
        'account_locked' => 'Account is locked',
        'administrator_role_locked' => 'Cannot modify administrators',
        'audit_retention_window_too_short' => 'Audit retention window is shorter than the minimum configured duration',
        'bot_protection_failed' => 'Request rejected by bot protection',
        'cannot_anonymise_last_administrator' => 'Cannot anonymise the last administrator',
        'cannot_deactivate_last_administrator' => 'Cannot deactivate the last administrator',
        'cannot_deactivate_self' => 'Cannot deactivate your own account',
        'invalid_two_factor_code' => 'Invalid two-factor code',
        'invitation_already_accepted' => 'Invitation has already been accepted',
        'invitation_already_pending' => 'An invitation is already pending for :email',
        'invitation_expired' => 'Invitation has expired',
        'system_role_immutable' => 'System roles cannot be modified',
        'two_factor_already_enabled' => 'Two-factor authentication is already enabled',
        'two_factor_required' => 'Two-factor authentication is required',
        'user_already_anonymised' => 'User has already been anonymised',
        'user_already_deactivated' => 'User is already deactivated',
    ],
    'mail' => [
        'invitation' => [
            'subject' => 'You are invited to join Support CRM',
            'greeting' => 'Welcome',
            'line' => 'You are invited to join Support CRM.',
            'action' => 'Accept Invitation',
        ],
    ],
];
