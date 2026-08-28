<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Knowledge\Services\KnowledgeCategoryTree;
use App\Domains\Knowledge\Services\Search\ArticleSearchIndexer;
use App\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class CreateArticle
{
    public function __construct(
        private readonly ArticleSearchIndexer $searchIndexer,
        private readonly KnowledgeCategoryTree $categoryTree,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(array $data, ?Authenticatable $actor = null): KnowledgeArticle
    {
        return \DB::transaction(function () use ($data, $actor) {
            if (isset($data['knowledge_category_id'])) {
                $category = KnowledgeCategory::findOrFail($data['knowledge_category_id']);
                $this->categoryTree->assertCanNest($category->parent);
            }

            $slug = Str::slug($data['title']['en']);
            $searchIndices = $this->searchIndexer->build(
                new KnowledgeArticle($data)
            );

            $article = KnowledgeArticle::create([
                'uuid' => Str::uuid(),
                'knowledge_category_id' => $data['knowledge_category_id'] ?? null,
                'slug' => $slug,
                'title' => $data['title'],
                'body' => $data['body'],
                'state' => ArticleState::Draft->value,
                'visibility' => $data['visibility'] ?? 'public',
                'current_version' => 0,
                'search_ar' => $searchIndices['search_ar'],
                'search_en' => $searchIndices['search_en'],
                'author_id' => $actor?->id,
            ]);

            $this->auditLogger->record(
                $actor,
                'knowledge.article.created',
                $article,
            );

            return $article;
        });
    }
}
