<?php

declare(strict_types=1);

return [
    'max_range_days' => (int) env('REPORTING_MAX_RANGE_DAYS', 366),
    'export' => [
        'sync_row_threshold' => (int) env('REPORTING_EXPORT_SYNC_ROWS', 5000),
        'max_rows' => (int) env('REPORTING_EXPORT_MAX_ROWS', 250000),
        'disk' => env('REPORTING_EXPORT_DISK', 'local'),
        'retention_days' => (int) env('REPORTING_EXPORT_RETENTION_DAYS', 7),
    ],
    'schedules' => [
        'max_recipients' => (int) env('REPORTING_SCHEDULE_MAX_RECIPIENTS', 25),
    ],
];
