<?php

return [
    'queue' => env('NOTIFICATIONS_QUEUE', 'notifications'),
    'defaults' => [
        'sla.warning' => ['in_app' => true, 'mail' => true],
        'sla.breach' => ['in_app' => true, 'mail' => true],
        'ticket.assigned' => ['in_app' => true, 'mail' => true],
        'ticket.transferred' => ['in_app' => true, 'mail' => false],
        'ticket.escalated' => ['in_app' => true, 'mail' => true],
        'ticket.message.posted' => ['in_app' => true, 'mail' => false],
        'user.invited' => ['in_app' => false, 'mail' => true],
    ],
    'retention_days' => (int) env('NOTIFICATIONS_RETENTION_DAYS', 180),
];
