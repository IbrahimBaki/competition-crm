<?php

return [
    'email' => [
        // Message-ID domain for outbound ticket emails
        'message_id_domain' => env('CHANNELS_EMAIL_MESSAGE_ID_DOMAIN', 'ticket.local'),

        // Loop prevention
        'loop' => [
            'max_per_window' => env('CHANNELS_EMAIL_LOOP_MAX_PER_WINDOW', 10),
            'window_minutes' => env('CHANNELS_EMAIL_LOOP_WINDOW_MINUTES', 5),
        ],

        // Logging
        'alert_log_channel' => env('CHANNELS_EMAIL_ALERT_LOG_CHANNEL', 'single'),

        // Retention
        'raw_retention_days' => env('CHANNELS_EMAIL_RAW_RETENTION_DAYS', 90),
    ],
];
