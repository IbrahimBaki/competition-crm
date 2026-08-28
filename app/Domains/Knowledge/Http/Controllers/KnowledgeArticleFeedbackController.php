<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Actions\RecordArticleFeedback;
use App\Domains\Knowledge\Http\Requests\StoreArticleFeedbackRequest;
use App\Domains\Knowledge\Http\Resources\ArticleFeedbackAcknowledgementResource;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use Illuminate\Http\JsonResponse;

class KnowledgeArticleFeedbackController
{
    public function __construct(
        private RecordArticleFeedback $recordFeedback,
    ) {}

    public function store(StoreArticleFeedbackRequest $request, KnowledgeArticle $article): JsonResponse
    {
        $feedback = $this->recordFeedback->execute(
            $article,
            $request->boolean('is_helpful'),
            auth()->user(),
            $request->input('visitor_key')
        );

        return response()->json([
            'data' => new ArticleFeedbackAcknowledgementResource($feedback),
        ], 201);
    }
}
