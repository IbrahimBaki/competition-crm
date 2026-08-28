<?php

namespace Tests\Feature\Ai;

use App\Domains\Ai\Exceptions\AiSuggestionNotApprovedException;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ticketing\Actions\PostTicketMessage;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSuggestionApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_suggestion_cannot_be_sent_to_customer(): void
    {
        $ticket = Ticket::factory()->create();
        $actor = User::factory()->create();
        $suggestion = AiSuggestion::factory()->create([
            'ticket_id' => $ticket->id,
            'state' => AiSuggestionState::Pending,
        ]);

        $action = app(PostTicketMessage::class);

        $this->expectException(AiSuggestionNotApprovedException::class);

        $action->handle(
            $ticket,
            $actor,
            MessageChannel::Webform,
            'Test message',
            aiSuggestionId: $suggestion->id,
        );
    }

    public function test_accepted_suggestion_can_be_sent_and_transitions_to_sent(): void
    {
        $ticket = Ticket::factory()->create();
        $actor = User::factory()->create();
        $resolver = User::factory()->create();
        $suggestion = AiSuggestion::factory()->accepted()->create([
            'ticket_id' => $ticket->id,
            'resolved_by_user_id' => $resolver->id,
        ]);

        $action = app(PostTicketMessage::class);

        $message = $action->handle(
            $ticket,
            $actor,
            MessageChannel::Webform,
            'Test message',
            aiSuggestionId: $suggestion->id,
        );

        $this->assertEquals($suggestion->id, $message->ai_suggestion_id);
        $suggestion->refresh();
        $this->assertEquals(AiSuggestionState::Sent, $suggestion->state);
    }

    public function test_suggestion_from_different_ticket_is_rejected(): void
    {
        $ticket1 = Ticket::factory()->create();
        $ticket2 = Ticket::factory()->create();
        $actor = User::factory()->create();
        $resolver = User::factory()->create();

        $suggestion = AiSuggestion::factory()->accepted()->create([
            'ticket_id' => $ticket2->id,
            'resolved_by_user_id' => $resolver->id,
        ]);

        $action = app(PostTicketMessage::class);

        $this->expectException(AiSuggestionNotApprovedException::class);

        $action->handle(
            $ticket1,
            $actor,
            MessageChannel::Webform,
            'Test message',
            aiSuggestionId: $suggestion->id,
        );
    }

    public function test_suggestion_without_resolver_is_rejected(): void
    {
        $ticket = Ticket::factory()->create();
        $actor = User::factory()->create();
        $suggestion = AiSuggestion::factory()->create([
            'ticket_id' => $ticket->id,
            'state' => AiSuggestionState::Accepted,
            'resolved_by_user_id' => null,
        ]);

        $action = app(PostTicketMessage::class);

        $this->expectException(AiSuggestionNotApprovedException::class);

        $action->handle(
            $ticket,
            $actor,
            MessageChannel::Webform,
            'Test message',
            aiSuggestionId: $suggestion->id,
        );
    }

    public function test_sent_suggestion_cannot_be_sent_twice(): void
    {
        $ticket = Ticket::factory()->create();
        $actor = User::factory()->create();
        $suggestion = AiSuggestion::factory()->sent()->create([
            'ticket_id' => $ticket->id,
        ]);

        $action = app(PostTicketMessage::class);

        $this->expectException(AiSuggestionNotApprovedException::class);

        $action->handle(
            $ticket,
            $actor,
            MessageChannel::Webform,
            'Test message',
            aiSuggestionId: $suggestion->id,
        );
    }
}
