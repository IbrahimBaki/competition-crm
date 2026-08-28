<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Exceptions\IllegalArticleTransitionException;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Models\KnowledgeArticleVersion;
use App\Domains\Knowledge\Services\Lifecycle\ArticleTransitionMap;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class PublishArticle
{
    public function __construct(
        private readonly ArticleTransitionMap $transitionMap,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(KnowledgeArticle $article, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($article, $actor) {
            if (! $this->transitionMap->allows($article->state, ArticleState::Published)) {
                throw new IllegalArticleTransitionException(
                    "Cannot transition from {$article->state->value} to published"
                );
            }

            $before = $article->toArray();

            $nextVersion = $article->current_version + 1;

            KnowledgeArticleVersion::create([
                'uuid' => Str::uuid(),
                'knowledge_article_id' => $article->id,
                'version' => $nextVersion,
                'title' => $article->title,
                'body' => $article->body,
                'visibility' => $article->visibility->value,
                'knowledge_category_id' => $article->knowledge_category_id,
                'published_by' => $actor?->id,
                'published_at' => now(),
            ]);

            $article->update([
                'state' => ArticleState::Published->value,
                'current_version' => $nextVersion,
                'published_at' => now(),
                'published_by' => $actor?->id,
            ]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.published',
                $article->refresh(),
                $before,
                $article->toArray()
            );

            return $article;
        });
    }
}
