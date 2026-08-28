<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Exceptions\IllegalArticleTransitionException;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Services\Lifecycle\ArticleTransitionMap;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class ArchiveArticle
{
    public function __construct(
        private readonly ArticleTransitionMap $transitionMap,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(KnowledgeArticle $article, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($article, $actor) {
            if (! $this->transitionMap->allows($article->state, ArticleState::Archived)) {
                throw new IllegalArticleTransitionException(
                    "Cannot transition from {$article->state->value} to archived"
                );
            }

            $before = $article->toArray();

            $article->update([
                'state' => ArticleState::Archived->value,
                'archived_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.archived',
                $article->refresh(),
                $before,
                $article->toArray()
            );

            return $article;
        });
    }
}
