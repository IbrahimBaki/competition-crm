<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Actions\CreateArticle;
use App\Domains\Knowledge\Actions\UpdateArticle;
use App\Domains\Knowledge\Http\Requests\StoreKnowledgeArticleRequest;
use App\Domains\Knowledge\Http\Requests\UpdateKnowledgeArticleRequest;
use App\Domains\Knowledge\Http\Resources\KnowledgeArticleResource;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeArticleController
{
    use AuthorizesRequests;

    public function __construct(
        private CreateArticle $createArticle,
        private UpdateArticle $updateArticle,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', KnowledgeArticle::class);

        $spec = (new CollectionQuerySpec)->withSorts(['id', 'title', 'state', 'created_at']);
        $query = new CollectionQuery($request, $spec);
        $articles = $query->paginate(KnowledgeArticle::query());

        return ApiResponse::paginated(
            KnowledgeArticleResource::collection($articles),
            $articles,
            $query->meta(),
        )->toResponse($request);
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
