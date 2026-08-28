<?php

namespace App\Domains\Knowledge\Services\Lifecycle;

use App\Domains\Knowledge\Models\ArticleState;

final class ArticleTransitionMap
{
    private const TRANSITIONS = [
        'draft' => ['in_review', 'archived'],
        'in_review' => ['draft', 'published', 'archived'],
        'published' => ['archived'],
        'archived' => ['draft'],
    ];

    public function allowedFrom(ArticleState $from): array
    {
        $targets = self::TRANSITIONS[$from->value] ?? [];

        return array_map(fn ($v) => ArticleState::from($v), $targets);
    }

    public function allows(ArticleState $from, ArticleState $to): bool
    {
        if ($from->value === $to->value) {
            return true;
        }

        $targets = self::TRANSITIONS[$from->value] ?? [];

        return in_array($to->value, $targets, true);
    }
}
