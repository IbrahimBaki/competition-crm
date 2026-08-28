<?php

namespace App\Domains\Ai\Http\Controllers;

use App\Domains\Ai\Actions\ClassifyTicket;
use App\Domains\Ai\Actions\GenerateSuggestedReply;
use App\Domains\Ai\Actions\GenerateTicketSummary;
use App\Domains\Ai\Actions\SuggestKnowledgeArticles;
use App\Domains\Ai\Http\Resources\AiSuggestionResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class TicketAiAssistController extends Controller
{
    use AuthorizesRequests;

    public function summary(Ticket $ticket, GenerateTicketSummary $action)
    {
        $this->authorize('view', $ticket);

        $suggestion = $action->handle($ticket);

        return ApiResponse::created(new AiSuggestionResource($suggestion));
    }

    public function suggestedReply(Ticket $ticket, GenerateSuggestedReply $action)
    {
        $this->authorize('view', $ticket);

        $suggestion = $action->handle($ticket);

        return ApiResponse::created(new AiSuggestionResource($suggestion));
    }

    public function classify(Ticket $ticket, ClassifyTicket $action)
    {
        $this->authorize('view', $ticket);

        $suggestion = $action->handle($ticket);

        return ApiResponse::created(new AiSuggestionResource($suggestion));
    }

    public function suggestedArticles(Ticket $ticket, SuggestKnowledgeArticles $action)
    {
        $this->authorize('view', $ticket);

        $suggestion = $action->handle($ticket);

        return ApiResponse::created(new AiSuggestionResource($suggestion));
    }
}
