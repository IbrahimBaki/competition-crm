<?php

return [
    'uploads' => [
        'disk' => env('ATTACHMENTS_DISK', 'attachments'),
        'max_size_kb' => (int) env('UPLOAD_MAX_SIZE_KB', 10240),
        'allowed_mimes' => [
            'application/pdf', 'image/png', 'image/jpeg',
            'text/plain', 'text/csv',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'allowed_extensions' => ['pdf', 'png', 'jpg', 'jpeg', 'txt', 'csv', 'docx', 'xlsx'],
    ],

    'scanning' => [
        'driver' => env('MALWARE_SCANNER', 'null'),
        'timeout_seconds' => (int) env('MALWARE_SCAN_TIMEOUT', 30),
    ],

    'public_endpoints' => [
        'throttle' => env('PUBLIC_THROTTLE', '20,1'),
        'bot_protection' => [
            'driver' => env('BOT_PROTECTION', 'null'),
            'secret' => env('BOT_PROTECTION_SECRET'),
        ],
    ],
];
