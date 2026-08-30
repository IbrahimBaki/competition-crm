<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Http\Resources\PublicKnowledgeArticleResource;
use App\Domains\Knowledge\Services\KnowledgeCategoryTree;
use App\Domains\Knowledge\Services\Search\SearchArticles;
use App\Domains\Knowledge\Services\Visibility\ArticleAudienceResolver;
use App\Domains\Knowledge\Services\Visibility\ArticleQueryScope;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use App\Support\Http\ApiResponse;
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

        return ApiResponse::paginated(
            PublicKnowledgeArticleResource::collection($paginated->items()),
            $paginated,
        )->toResponse(request());
    }

    public function search(Request $request, CollectionQuery $collectionQuery): JsonResponse
    {
        $audience = $this->audienceResolver->resolve(auth()->user(), false);
        $query = $request->string('q', $request->input('filter.q', ''))->toString();
        $spec = new CollectionQuerySpec(['id', 'title', 'created_at']);

        $results = $this->searchArticles->search($query, $audience);
        $paginated = $collectionQuery->paginate($results, $spec);

        return ApiResponse::paginated(
            PublicKnowledgeArticleResource::collection($paginated->items()),
            $paginated,
        )->toResponse($request);
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
