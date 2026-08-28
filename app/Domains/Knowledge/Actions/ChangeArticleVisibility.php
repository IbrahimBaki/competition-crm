<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Models\ArticleVisibility;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class ChangeArticleVisibility
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(KnowledgeArticle $article, ArticleVisibility $visibility, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($article, $visibility, $actor) {
            $before = $article->toArray();

            $article->update(['visibility' => $visibility->value]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.visibility_changed',
                $article->refresh(),
                $before,
                $article->toArray()
            );

            return $article;
        });
    }
}
