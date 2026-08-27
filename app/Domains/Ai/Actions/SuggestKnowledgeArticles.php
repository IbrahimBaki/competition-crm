<?php

namespace App\Domains\Ai\Actions;

use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionFeature;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Knowledge\Models\ArticleQueryScope;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Ticketing\Models\Ticket;

class SuggestKnowledgeArticles
{
    public function __construct(
        private readonly ArticleQueryScope $queryScope,
    ) {}

    public function handle(Ticket $ticket): AiSuggestion
    {
        $articles = KnowledgeArticle::where(function ($q) use ($ticket) {
            $this->queryScope->scopePublished($q);
            $q->whereRaw('MATCH(title) AGAINST(? IN BOOLEAN MODE)', [$ticket->subject]);
        })
            ->limit(3)
            ->get();

        $articlesList = $articles->map(fn ($a) => "{$a->title} ({$a->uuid})")->join(', ');

        return AiSuggestion::create([
            'ticket_id' => $ticket->id,
            'feature' => AiSuggestionFeature::KnowledgeArticles,
            'state' => AiSuggestionState::Pending,
            'content' => $articlesList,
        ]);
    }
}
