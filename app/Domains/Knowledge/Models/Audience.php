<?php

namespace App\Domains\Knowledge\Models;

enum Audience: string
{
    case Anonymous = 'anonymous';
    case Customer = 'customer';
    case Staff = 'staff';

    /**
     * @return array<int, string>
     */
    public function visibilityValues(): array
    {
        return match ($this) {
            self::Anonymous => [ArticleVisibility::Public->value],
            self::Customer => [ArticleVisibility::Public->value, ArticleVisibility::Customers->value],
            self::Staff => [
                ArticleVisibility::Public->value,
                ArticleVisibility::Customers->value,
                ArticleVisibility::Internal->value,
            ],
        };
    }
}
