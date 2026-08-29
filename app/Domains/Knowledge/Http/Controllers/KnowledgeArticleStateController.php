<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Actions\ArchiveArticle;
use App\Domains\Knowledge\Actions\PublishArticle;
use App\Domains\Knowledge\Actions\SubmitArticleForReview;
use App\Domains\Knowledge\Http\Requests\ChangeArticleStateRequest;
use App\Domains\Knowledge\Http\Resources\KnowledgeArticleResource;
use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class KnowledgeArticleStateController
{
    use AuthorizesRequests { authorize as authorizeAbility; }

    public function __construct(
        private SubmitArticleForReview $submitForReview,
        private PublishArticle $publishArticle,
        private ArchiveArticle $archiveArticle,
    ) {}

    public function store(ChangeArticleStateRequest $request, KnowledgeArticle $article): JsonResponse
    {
        $state = ArticleState::from($request->validated('state'));

        $updated = match ($state) {
            ArticleState::InReview => $this->submitForReview->execute($article, $request->user()),
            ArticleState::Published => $this->performAuthorized('publish', $article, fn () => $this->publishArticle->execute($article, $request->user())),
            ArticleState::Archived => $this->performAuthorized('archive', $article, fn () => $this->archiveArticle->execute($article, $request->user())),
            default => $article,
        };

        return response()->json([
            'data' => new KnowledgeArticleResource($updated),
        ]);
    }

    private function performAuthorized(string $ability, KnowledgeArticle $article, callable $callback)
    {
        $this->authorizeAbility($ability, $article);

        return $callback();
    }
}
