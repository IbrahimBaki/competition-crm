<?php

return [
    'disk' => env('BACKUP_DISK', 'backups'),

    'include_attachments' => env('BACKUP_INCLUDE_ATTACHMENTS', true),

    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    'verify' => [
        'enabled' => env('BACKUP_VERIFY_ENABLED', true),
    ],
];
