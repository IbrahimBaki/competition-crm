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

    'web_form' => [
        // Duplicate protection window for identical submissions.
        'dedupe' => [
            'window_minutes' => env('CHANNELS_WEB_FORM_DEDUPE_WINDOW_MINUTES', 10),
        ],

        // Dedicated rate limiter for the public submit endpoint.
        'rate_limit' => [
            'max_per_minute' => env('CHANNELS_WEB_FORM_MAX_PER_MINUTE', 5),
            'max_per_hour' => env('CHANNELS_WEB_FORM_MAX_PER_HOUR', 30),
        ],

        // Hard ceiling on any single free-text answer, in characters.
        'max_field_length' => env('CHANNELS_WEB_FORM_MAX_FIELD_LENGTH', 5000),

        // Retention for stored raw submission payloads, in days.
        'submission_retention_days' => env('CHANNELS_WEB_FORM_SUBMISSION_RETENTION_DAYS', 90),
    ],
];
