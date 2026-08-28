<?php

return [
    'budgets' => [
        'tickets.index' => ['ms' => 400, 'queries' => 25],
        'tickets.show' => ['ms' => 300, 'queries' => 30],
        'reports.dashboard' => ['ms' => 800, 'queries' => 40],
    ],

    'dataset' => [
        'tickets' => 5000,
        'customers' => 1000,
        'messages_per_ticket' => 6,
    ],
];
