<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Actions\RestoreArticleVersion;
use App\Domains\Knowledge\Http\Resources\KnowledgeArticleVersionResource;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class KnowledgeArticleVersionController
{
    use AuthorizesRequests;

    public function __construct(
        private RestoreArticleVersion $restoreVersion,
    ) {}

    public function index(KnowledgeArticle $article): JsonResponse
    {
        $this->authorize('view', $article);

        $versions = $article->versions()->orderBy('version', 'desc')->get();

        return response()->json([
            'data' => KnowledgeArticleVersionResource::collection($versions),
        ]);
    }

    public function store(KnowledgeArticle $article, int $version): JsonResponse
    {
        $this->authorize('restoreVersion', $article);

        $updated = $this->restoreVersion->execute(
            $article,
            $version,
            auth()->user()
        );

        return response()->json([
            'data' => new KnowledgeArticleVersionResource($article->versions()->where('version', $version)->first()),
        ]);
    }
}
