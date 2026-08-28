<?php

declare(strict_types=1);

return [
    // Master switch. When false, every AI endpoint responds with the
    // "feature disabled" error and no provider call is ever attempted.
    'enabled' => (bool) env('AI_ENABLED', false),

    'provider' => env('AI_PROVIDER', 'null'),

    // Individually toggleable agent-facing features (acceptance criterion 2).
    'features' => [
        'summary' => (bool) env('AI_FEATURE_SUMMARY', false),
        'suggested_reply' => (bool) env('AI_FEATURE_SUGGESTED_REPLY', false),
        'classification' => (bool) env('AI_FEATURE_CLASSIFICATION', false),
        'suggested_articles' => (bool) env('AI_FEATURE_SUGGESTED_ARTICLES', false),
        'chatbot' => (bool) env('AI_FEATURE_CHATBOT', false),
    ],

    'classification' => [
        // Below this confidence the ticket is routed to a human, never auto-labelled.
        'confidence_threshold' => (float) env('AI_CLASSIFICATION_THRESHOLD', 0.75),
    ],

    'chatbot' => [
        // Consecutive unanswered turns before mandatory human handoff.
        'max_failed_turns' => (int) env('AI_CHATBOT_MAX_FAILED_TURNS', 2),
        'min_article_score' => (float) env('AI_CHATBOT_MIN_ARTICLE_SCORE', 0.2),
    ],

    'budget' => [
        // Micro-units of account currency per period. Zero disables spending.
        'period' => env('AI_BUDGET_PERIOD', 'month'),
        'limit_micros' => (int) env('AI_BUDGET_LIMIT_MICROS', 0),
        'block_when_exceeded' => (bool) env('AI_BUDGET_BLOCK', true),
    ],

    'privacy' => [
        // Redact personal data before any payload leaves the system.
        'redact_personal_data' => (bool) env('AI_REDACT_PERSONAL_DATA', true),
    ],

    'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 15),
];
