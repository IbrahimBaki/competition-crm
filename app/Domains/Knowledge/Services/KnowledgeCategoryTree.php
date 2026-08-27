<?php

namespace App\Domains\Knowledge\Services;

use App\Domains\Knowledge\Exceptions\KnowledgeCategoryDepthExceededException;
use App\Domains\Knowledge\Models\Audience;
use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Knowledge\Services\Visibility\ArticleQueryScope;
use Illuminate\Support\Collection;

class KnowledgeCategoryTree
{
    public function __construct(
        private ArticleQueryScope $queryScope,
    ) {}

    public function maxDepth(): int
    {
        return 3;
    }

    public function assertCanNest(?KnowledgeCategory $parent): void
    {
        if ($parent !== null && $parent->depth >= $this->maxDepth()) {
            throw new KnowledgeCategoryDepthExceededException;
        }
    }

    public function getActiveTree(Audience $audience): Collection
    {
        $roots = KnowledgeCategory::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return $roots->map(fn (KnowledgeCategory $root) => $this->buildBranch($root, $audience));
    }

    private function buildBranch(KnowledgeCategory $category, Audience $audience): array
    {
        $articleCount = $this->queryScope->forAudience($audience)
            ->where('knowledge_category_id', $category->id)
            ->count();

        $children = $category->children()
            ->where('is_active', true)
            ->orderBy('position')
            ->get()
            ->map(fn (KnowledgeCategory $child) => $this->buildBranch($child, $audience));

        return [
            'id' => $category->uuid,
            'code' => $category->code,
            'name' => $category->name,
            'depth' => $category->depth,
            'article_count' => $articleCount,
            'children' => $children,
        ];
    }
}
