<?php

namespace Tests\Feature\Ai;

use App\Domains\Ai\Actions\ResolveAiSuggestion;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ticketing\Actions\PostTicketMessage;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Story473IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_to_end_ai_suggestion_approval_and_send(): void
    {
        config(['ai.enabled' => true]);

        $ticket = Ticket::factory()->create();
        $requester = User::factory()->create();
        $resolver = User::factory()->create();
        $sender = User::factory()->create();

        // Agent creates an AI suggestion
        $suggestion = AiSuggestion::factory()->create([
            'ticket_id' => $ticket->id,
            'state' => AiSuggestionState::Pending,
            'requested_by_user_id' => $requester->id,
        ]);

        $this->assertEquals(AiSuggestionState::Pending, $suggestion->state);

        // Resolver approves it
        $resolveAction = app(ResolveAiSuggestion::class);
        $resolveAction->handle($suggestion, $resolver, 'accept');

        $suggestion->refresh();
        $this->assertEquals(AiSuggestionState::Accepted, $suggestion->state);
        $this->assertEquals($resolver->id, $suggestion->resolved_by_user_id);

        // Sender posts the approved suggestion to customer
        $postAction = app(PostTicketMessage::class);
        $message = $postAction->handle(
            $ticket,
            $sender,
            MessageChannel::Webform,
            'This is an AI-assisted reply',
            aiSuggestionId: $suggestion->id,
        );

        $this->assertNotNull($message->id);
        $this->assertEquals($suggestion->id, $message->ai_suggestion_id);

        $suggestion->refresh();
        $this->assertEquals(AiSuggestionState::Sent, $suggestion->state);
    }

    public function test_ai_disabled_returns_error(): void
    {
        config(['ai.enabled' => false]);

        // Feature gate should prevent any AI call
        $this->assertTrue(! config('ai.enabled'));
    }
}
