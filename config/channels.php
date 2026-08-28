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

    'whatsapp' => [
        // Free-form (non-template) reply window, in hours, measured from the
        // last inbound customer message on the ticket.
        'free_form_window_hours' => env('CHANNELS_WHATSAPP_FREE_FORM_WINDOW_HOURS', 24),

        // Outbound transport: 'null' logs instead of calling a provider.
        'transport' => env('CHANNELS_WHATSAPP_TRANSPORT', 'null'),

        // Shared secret for provider inbound + receipt webhooks.
        'webhook_secret' => env('CHANNELS_WHATSAPP_WEBHOOK_SECRET'),

        // Retention for stored raw provider payloads, in days.
        'raw_retention_days' => env('CHANNELS_WHATSAPP_RAW_RETENTION_DAYS', 90),
    ],

    'sms' => [
        'transport' => env('CHANNELS_SMS_TRANSPORT', 'null'),
        'webhook_secret' => env('CHANNELS_SMS_WEBHOOK_SECRET'),
        'max_body_length' => env('CHANNELS_SMS_MAX_BODY_LENGTH', 1600),
        'raw_retention_days' => env('CHANNELS_SMS_RAW_RETENTION_DAYS', 90),
    ],

    'chat' => [
        // Behaviour when no agent can take the chat: 'queue', 'offline_form' or 'both'.
        'no_agent_behaviour' => env('CHANNELS_CHAT_NO_AGENT_BEHAVIOUR', 'both'),

        // Maximum visitors allowed to wait before new requests fall back to the offline form.
        'max_queue_length' => env('CHANNELS_CHAT_MAX_QUEUE_LENGTH', 20),

        // Concurrent active sessions a single agent may hold.
        'max_concurrent_per_agent' => env('CHANNELS_CHAT_MAX_CONCURRENT_PER_AGENT', 3),

        // Window in which a dropped visitor may rejoin the same session.
        'reconnect_window_seconds' => env('CHANNELS_CHAT_RECONNECT_WINDOW_SECONDS', 300),

        // Silence after which a queued or active session is swept to 'abandoned'.
        'abandon_after_seconds' => env('CHANNELS_CHAT_ABANDON_AFTER_SECONDS', 900),

        // Dedicated rate limiter for the public visitor endpoints.
        'rate_limit' => [
            'max_per_minute' => env('CHANNELS_CHAT_MAX_PER_MINUTE', 30),
            'max_per_hour' => env('CHANNELS_CHAT_MAX_PER_HOUR', 300),
        ],

        // Hard ceiling on a single chat turn, in characters.
        'max_message_length' => env('CHANNELS_CHAT_MAX_MESSAGE_LENGTH', 4000),

        // Retention for buffered chat rows after the transcript has been persisted, in days.
        'transcript_retention_days' => env('CHANNELS_CHAT_TRANSCRIPT_RETENTION_DAYS', 90),
    ],

    // Characters taken from an inbound body when the channel carries no subject.
    'derived_subject_length' => env('CHANNELS_DERIVED_SUBJECT_LENGTH', 80),
];
