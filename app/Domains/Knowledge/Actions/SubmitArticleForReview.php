<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Exceptions\IllegalArticleTransitionException;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Services\Lifecycle\ArticleTransitionMap;
use App\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class SubmitArticleForReview
{
    public function __construct(
        private readonly ArticleTransitionMap $transitionMap,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(KnowledgeArticle $article, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($article, $actor) {
            if (! $this->transitionMap->allows($article->state, ArticleState::InReview)) {
                throw new IllegalArticleTransitionException(
                    "Cannot transition from {$article->state->value} to in_review"
                );
            }

            $before = $article->toArray();

            $article->update(['state' => ArticleState::InReview->value]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.submitted_for_review',
                $article->refresh(),
                $before,
                $article->toArray()
            );

            return $article;
        });
    }
}
