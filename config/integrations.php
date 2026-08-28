<?php

return [
    'webhooks' => [
        'enabled' => env('INTEGRATIONS_WEBHOOKS_ENABLED', false),
        'timeout_seconds' => (int) env('INTEGRATIONS_WEBHOOK_TIMEOUT', 5),
        'max_attempts' => (int) env('INTEGRATIONS_WEBHOOK_MAX_ATTEMPTS', 5),
        'auto_disable_after_failures' => (int) env('INTEGRATIONS_WEBHOOK_AUTO_DISABLE', 20),
    ],
    'erp' => [
        'enabled' => env('INTEGRATIONS_ERP_ENABLED', false),
        'base_url' => env('INTEGRATIONS_ERP_BASE_URL'),
        'api_key' => env('INTEGRATIONS_ERP_API_KEY'),
        'timeout_seconds' => (int) env('INTEGRATIONS_ERP_TIMEOUT', 5),
        'cache_ttl_seconds' => (int) env('INTEGRATIONS_ERP_CACHE_TTL', 300),
    ],
    'import' => [
        'max_rows' => (int) env('INTEGRATIONS_IMPORT_MAX_ROWS', 50000),
        'chunk_size' => (int) env('INTEGRATIONS_IMPORT_CHUNK', 500),
    ],
];
