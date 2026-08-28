<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Services\ArticleReplyRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeArticleRenderController
{
    public function __construct(
        private ArticleReplyRenderer $renderer,
    ) {}

    public function store(Request $request, ArticleReplyRenderer $renderer): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => 'required|uuid',
            'locale' => 'nullable|in:ar,en',
        ]);

        $article = KnowledgeArticle::where('uuid', $validated['article_id'])->firstOrFail();

        $this->authorize('view', $article);

        return response()->json([
            'data' => [
                'en' => $renderer->render($article, 'en'),
                'ar' => $renderer->render($article, 'ar'),
            ],
        ]);
    }
}
