<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Exceptions\ArticleVersionNotFoundException;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Services\Search\ArticleSearchIndexer;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class RestoreArticleVersion
{
    public function __construct(
        private readonly ArticleSearchIndexer $searchIndexer,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(KnowledgeArticle $article, int $version, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($article, $version, $actor) {
            $versionRow = $article->versions()
                ->where('version', $version)
                ->first();

            if (! $versionRow) {
                throw new ArticleVersionNotFoundException;
            }

            $before = $article->toArray();

            $tempArticle = new KnowledgeArticle([
                'title' => $versionRow->title,
                'body' => $versionRow->body,
            ]);
            $searchIndices = $this->searchIndexer->build($tempArticle);

            $article->update([
                'title' => $versionRow->title,
                'body' => $versionRow->body,
                'visibility' => $versionRow->visibility,
                'knowledge_category_id' => $versionRow->knowledge_category_id,
                'state' => ArticleState::Draft->value,
                'search_ar' => $searchIndices['search_ar'],
                'search_en' => $searchIndices['search_en'],
            ]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.version_restored',
                $article->refresh(),
                $before,
                $article->toArray()
            );

            return $article;
        });
    }
}
