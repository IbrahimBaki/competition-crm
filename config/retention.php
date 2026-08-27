<?php

return [
    'classes' => [
        'tickets' => ['days' => env('RETENTION_TICKETS_DAYS', 2555)],
        'messages' => ['days' => env('RETENTION_MESSAGES_DAYS', 2555)],
        'attachments' => ['days' => env('RETENTION_ATTACHMENTS_DAYS', 1095)],
        'logs' => ['days' => env('RETENTION_LOGS_DAYS', 90)],
        'audit' => ['days' => env('RETENTION_AUDIT_DAYS', 2555)],
        'provider_inbound_messages' => ['days' => env('CHANNELS_WHATSAPP_RAW_RETENTION_DAYS', 90)],
        'provider_inbound_messages_sms' => ['days' => env('CHANNELS_SMS_RAW_RETENTION_DAYS', 90)],
        'chat_sessions' => ['days' => env('CHANNELS_CHAT_TRANSCRIPT_RETENTION_DAYS', 90)],
        'ai_suggestions' => ['days' => env('RETENTION_AI_SUGGESTIONS_DAYS', 90)],
        'ai_usage_records' => ['days' => env('RETENTION_AI_USAGE_DAYS', 180)],
    ],

    'audit_minimum_days' => env('RETENTION_AUDIT_MINIMUM_DAYS', 365),

    'batch_size' => env('RETENTION_BATCH_SIZE', 500),

    'dry_run' => env('RETENTION_DRY_RUN', false),
];
