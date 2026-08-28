<?php

namespace App\Domains\Knowledge\Services;

use App\Domains\Knowledge\Exceptions\ArticleNotPublishedException;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Support\I18n\LocaleResolver;

readonly class ArticleReplyRenderer
{
    public function __construct(
        private LocaleResolver $localeResolver,
    ) {}

    public function render(KnowledgeArticle $article, ?string $locale = null): string
    {
        if ($article->state !== ArticleState::Published) {
            throw new ArticleNotPublishedException;
        }

        $locale ??= $this->localeResolver->resolveLocale();

        return match ($locale) {
            'ar' => $article->body['ar'] ?? '',
            default => $article->body['en'] ?? '',
        };
    }
}
