<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Http\Resources\PublicKnowledgeArticleResource;
use App\Domains\Knowledge\Services\KnowledgeCategoryTree;
use App\Domains\Knowledge\Services\Search\SearchArticles;
use App\Domains\Knowledge\Services\Visibility\ArticleAudienceResolver;
use App\Domains\Knowledge\Services\Visibility\ArticleQueryScope;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicKnowledgeArticleController
{
    public function __construct(
        private ArticleAudienceResolver $audienceResolver,
        private ArticleQueryScope $queryScope,
        private KnowledgeCategoryTree $categoryTree,
        private SearchArticles $searchArticles,
    ) {}

    public function categories(): JsonResponse
    {
        $audience = $this->audienceResolver->resolve(auth()->user(), false);
        $tree = $this->categoryTree->getActiveTree($audience);

        return response()->json(['data' => $tree]);
    }

    public function index(CollectionQuery $collectionQuery): JsonResponse
    {
        $audience = $this->audienceResolver->resolve(auth()->user(), false);
        $spec = new CollectionQuerySpec(['id', 'title', 'created_at']);

        $articles = $this->queryScope->forAudience($audience);
        $paginated = $collectionQuery->paginate($articles, $spec);

        return response()->json([
            'data' => PublicKnowledgeArticleResource::collection($paginated->items()),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
            ],
        ]);
    }

    public function search(Request $request, CollectionQuery $collectionQuery): JsonResponse
    {
        $audience = $this->audienceResolver->resolve(auth()->user(), false);
        $query = $request->input('filter.q', '');
        $spec = new CollectionQuerySpec(['id', 'title', 'created_at']);

        $results = $this->searchArticles->search($query, $audience);
        $paginated = $collectionQuery->paginate($results, $spec);

        return response()->json([
            'data' => PublicKnowledgeArticleResource::collection($paginated->items()),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
            ],
        ]);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $audience = $this->audienceResolver->resolve(auth()->user(), false);

        $article = $this->queryScope->forAudience($audience)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'data' => new PublicKnowledgeArticleResource($article),
        ]);
    }
}
