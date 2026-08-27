<?php

namespace App\Domains\Ai\Services\Chatbot;

use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Services\AiClient;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Knowledge\Services\Search\SearchArticles;
use App\Domains\Knowledge\Services\Visibility\ArticleQueryScope;

class ChatbotAnswerer
{
    public function __construct(
        private readonly AiClient $aiClient,
        private readonly SearchArticles $searchService,
        private readonly ArticleQueryScope $scopeService,
    ) {}

    public function answer(ChatSession $session, string $visitorMessage): ?string
    {
        $this->aiClient->complete(
            AiFeature::Chatbot,
            [$visitorMessage],
        );

        // Search for relevant articles
        $articles = $this->searchService->search($visitorMessage);

        // Filter to published, customer-visible articles only
        $filtered = $articles->filter(fn ($article) => $this->scopeService->isVisibleToCustomer($article));

        if ($filtered->isEmpty()) {
            return null;
        }

        // Return the top article as answer
        $article = $filtered->first();

        return $article->render();
    }
}
