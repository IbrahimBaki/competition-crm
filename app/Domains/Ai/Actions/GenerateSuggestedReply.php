<?php

namespace App\Domains\Ai\Actions;

use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionFeature;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ai\Services\Provider\AiClient;
use App\Domains\Ticketing\Models\Ticket;

class GenerateSuggestedReply
{
    public function __construct(
        private readonly AiClient $aiClient,
    ) {}

    public function handle(Ticket $ticket): AiSuggestion
    {
        $reply = $this->aiClient->complete(
            prompt: "Suggest a professional reply to this ticket: {$ticket->body}",
            budget: 'suggested_reply',
            feature: AiSuggestionFeature::SuggestedReply,
        );

        return AiSuggestion::create([
            'ticket_id' => $ticket->id,
            'feature' => AiSuggestionFeature::SuggestedReply,
            'state' => AiSuggestionState::Pending,
            'content' => $reply,
        ]);
    }
}
