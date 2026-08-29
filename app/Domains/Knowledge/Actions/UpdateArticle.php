<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Exceptions\IllegalArticleTransitionException;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Knowledge\Services\Search\ArticleSearchIndexer;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateArticle
{
    public function __construct(
        private readonly ArticleSearchIndexer $searchIndexer,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(KnowledgeArticle $article, array $data, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($article, $data, $actor) {
            if ($article->state === ArticleState::Archived) {
                throw new IllegalArticleTransitionException(
                    'Cannot edit archived article'
                );
            }

            $before = $article->toArray();

            $categoryId = $article->knowledge_category_id;
            if (array_key_exists('knowledge_category_id', $data)) {
                $categoryId = empty($data['knowledge_category_id'])
                    ? null
                    : KnowledgeCategory::where('uuid', $data['knowledge_category_id'])->firstOrFail()->id;
            }

            $updateData = [
                'title' => $data['title'] ?? $article->title,
                'body' => $data['body'] ?? $article->body,
                'visibility' => $data['visibility'] ?? $article->visibility,
                'knowledge_category_id' => $categoryId,
            ];

            $tempArticle = new KnowledgeArticle($updateData);
            $searchIndices = $this->searchIndexer->build($tempArticle);

            $article->update([
                ...$updateData,
                'search_ar' => $searchIndices['search_ar'],
                'search_en' => $searchIndices['search_en'],
            ]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.updated',
                $article->refresh(),
                $before,
                $article->toArray()
            );

            return $article;
        });
    }
}
