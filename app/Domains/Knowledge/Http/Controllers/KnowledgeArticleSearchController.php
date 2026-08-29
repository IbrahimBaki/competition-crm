<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Http\Resources\KnowledgeArticleResource;
use App\Domains\Knowledge\Models\Audience;
use App\Domains\Knowledge\Services\Search\SearchArticles;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeArticleSearchController
{
    use AuthorizesRequests;

    public function __construct(
        private SearchArticles $searchArticles,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Domains\Knowledge\Models\KnowledgeArticle::class);
        $query = $request->input('filter.q', '');
        $spec = (new CollectionQuerySpec)->withSorts(['id', 'title', 'created_at']);
        $collectionQuery = new CollectionQuery($request, $spec);

        $results = $this->searchArticles->search($query, Audience::Staff);
        $paginated = $collectionQuery->paginate($results);

        return ApiResponse::paginated(
            KnowledgeArticleResource::collection($paginated),
            $paginated,
            $collectionQuery->meta(),
        )->toResponse($request);
    }
}
