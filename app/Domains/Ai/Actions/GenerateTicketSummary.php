<?php

namespace App\Domains\Ai\Actions;

use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionFeature;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ai\Services\AiClient;
use App\Domains\Ticketing\Models\Ticket;

class GenerateTicketSummary
{
    public function __construct(
        private readonly AiClient $aiClient,
    ) {}

    public function handle(Ticket $ticket): AiSuggestion
    {
        $summary = $this->aiClient->complete(
            prompt: "Summarize this ticket concisely: {$ticket->body}",
            budget: 'ticket_summary',
            feature: AiSuggestionFeature::TicketSummary,
        );

        return AiSuggestion::create([
            'ticket_id' => $ticket->id,
            'feature' => AiSuggestionFeature::TicketSummary,
            'state' => AiSuggestionState::Pending,
            'content' => $summary,
        ]);
    }
}
