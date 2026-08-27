<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Actions\CreateArticle;
use App\Domains\Knowledge\Actions\UpdateArticle;
use App\Domains\Knowledge\Http\Requests\StoreKnowledgeArticleRequest;
use App\Domains\Knowledge\Http\Requests\UpdateKnowledgeArticleRequest;
use App\Domains\Knowledge\Http\Resources\KnowledgeArticleResource;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Http\JsonResponse;

class KnowledgeArticleController
{
    public function __construct(
        private CreateArticle $createArticle,
        private UpdateArticle $updateArticle,
    ) {}

    public function index(CollectionQuery $query): JsonResponse
    {
        $this->authorize('viewAny', KnowledgeArticle::class);

        $spec = new CollectionQuerySpec(['id', 'title', 'state', 'created_at']);
        $articles = $query->paginate(KnowledgeArticle::class, $spec);

        return response()->json([
            'data' => KnowledgeArticleResource::collection($articles->items()),
            'meta' => [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
            ],
            'links' => [
                'first' => $articles->url(1),
                'last' => $articles->url($articles->lastPage()),
                'prev' => $articles->previousPageUrl(),
                'next' => $articles->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreKnowledgeArticleRequest $request): JsonResponse
    {
        $article = $this->createArticle->execute(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'data' => new KnowledgeArticleResource($article),
        ], 201);
    }

    public function show(KnowledgeArticle $article): JsonResponse
    {
        $this->authorize('view', $article);

        return response()->json([
            'data' => new KnowledgeArticleResource($article),
        ]);
    }

    public function update(UpdateKnowledgeArticleRequest $request, KnowledgeArticle $article): JsonResponse
    {
        $updated = $this->updateArticle->execute(
            $article,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'data' => new KnowledgeArticleResource($updated),
        ]);
    }
}
