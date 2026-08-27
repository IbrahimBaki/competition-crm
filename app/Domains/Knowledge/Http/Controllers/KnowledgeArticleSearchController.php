<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Http\Resources\KnowledgeArticleResource;
use App\Domains\Knowledge\Models\Audience;
use App\Domains\Knowledge\Services\Search\SearchArticles;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeArticleSearchController
{
    public function __construct(
        private SearchArticles $searchArticles,
    ) {}

    public function index(Request $request, CollectionQuery $collectionQuery): JsonResponse
    {
        $query = $request->input('filter.q', '');
        $spec = new CollectionQuerySpec(['id', 'title', 'created_at']);

        $results = $this->searchArticles->search($query, Audience::Staff);
        $paginated = $collectionQuery->paginate($results, $spec);

        return response()->json([
            'data' => KnowledgeArticleResource::collection($paginated->items()),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
            ],
        ]);
    }
}
