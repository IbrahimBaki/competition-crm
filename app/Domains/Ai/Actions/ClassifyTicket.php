<?php

namespace App\Domains\Ai\Actions;

use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ai\Models\ClassificationSource;
use App\Domains\Ai\Services\AiClient;
use App\Domains\Ticketing\Actions\ReclassifyTicket;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Models\User;

class ClassifyTicket
{
    public function __construct(
        private readonly AiClient $aiClient,
        private readonly ReclassifyTicket $reclassifyAction,
        private readonly RecordTicketEvent $eventRecorder,
    ) {}

    public function handle(Ticket $ticket, User $actor): void
    {
        $result = $this->aiClient->complete(
            AiFeature::Classification,
            [$ticket->subject, implode("\n", $ticket->messages()->pluck('body')->toArray())],
        );

        $confidence = $result->confidence ?? 0;
        $threshold = config('ai.classification.confidence_threshold');

        // Clamp confidence to 0-1 range
        $confidence = max(0, min(1, $confidence));

        // Determine if classification should be applied
        if ($confidence >= $threshold) {
            $categoryId = $this->resolveCategoryFromResult($result->content);

            if ($categoryId !== null) {
                $this->reclassifyAction->handle($ticket, $categoryId, $actor);

                $ticket->update([
                    'ai_classification_confidence' => $confidence,
                    'ai_classified_at' => now(),
                    'classification_source' => ClassificationSource::Ai,
                ]);

                $this->eventRecorder->handle(
                    $ticket,
                    TicketEventType::AiClassified,
                    null,
                    ['confidence' => $confidence],
                );

                return;
            }
        }

        // Below threshold or category not found - create suggestion for human review
        $suggestion = AiSuggestion::query()->create([
            'ticket_id' => $ticket->id,
            'feature' => AiFeature::Classification,
            'state' => AiSuggestionState::Pending,
            'content' => $result->content,
            'confidence' => $confidence,
            'model' => $result->model,
            'requested_by_user_id' => $actor->id,
        ]);

        $this->eventRecorder->handle(
            $ticket,
            TicketEventType::AiClassificationNeedsReview,
            null,
            ['suggestion_id' => $suggestion->id, 'confidence' => $confidence],
        );
    }

    private function resolveCategoryFromResult(string $content): ?int
    {
        // Attempt to extract category name from AI response
        // This is a simplistic implementation - real version would parse structured output
        $categories = TicketCategory::query()->active()->pluck('name', 'id');

        foreach ($categories as $id => $name) {
            if (stripos($content, $name) !== false) {
                return $id;
            }
        }

        return null;
    }
}
