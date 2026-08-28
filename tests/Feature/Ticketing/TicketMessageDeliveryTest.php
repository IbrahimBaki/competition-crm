<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Actions\PostTicketMessage;
use App\Domains\Ticketing\Actions\TransitionMessageDelivery;
use App\Domains\Ticketing\Exceptions\IllegalDeliveryTransitionException;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketMessageDeliveryTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_message_transitions_through_delivery_states(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();

        $postMessage = $this->app->make(PostTicketMessage::class);
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $agent,
            channel: MessageChannel::Email,
            body: 'Test',
        );

        $this->assertEquals(MessageDeliveryState::Queued, $message->delivery_state);

        $transition = $this->app->make(TransitionMessageDelivery::class);
        $message = $transition->handle($message, MessageDeliveryState::Sent);
        $this->assertEquals(MessageDeliveryState::Sent, $message->delivery_state);

        $message = $transition->handle($message, MessageDeliveryState::Delivered);
        $this->assertEquals(MessageDeliveryState::Delivered, $message->delivery_state);
    }

    public function test_delivery_event_recorded_per_transition(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();

        $postMessage = $this->app->make(PostTicketMessage::class);
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $agent,
            channel: MessageChannel::Email,
            body: 'Test',
        );

        // Should have one event from initial queuing
        $this->assertCount(1, $message->deliveryEvents);
        $this->assertNull($message->deliveryEvents[0]->from_state);
        $this->assertEquals(MessageDeliveryState::Queued, $message->deliveryEvents[0]->to_state);

        $transition = $this->app->make(TransitionMessageDelivery::class);
        $message = $transition->handle($message, MessageDeliveryState::Sent);

        // Should have two events now
        $this->assertCount(2, $message->deliveryEvents);
        $this->assertEquals(MessageDeliveryState::Queued, $message->deliveryEvents[1]->from_state);
        $this->assertEquals(MessageDeliveryState::Sent, $message->deliveryEvents[1]->to_state);
    }

    public function test_failure_reason_recorded_on_failed_state(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();

        $postMessage = $this->app->make(PostTicketMessage::class);
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $agent,
            channel: MessageChannel::Email,
            body: 'Test',
        );

        $transition = $this->app->make(TransitionMessageDelivery::class);
        $message = $transition->handle(
            $message,
            MessageDeliveryState::Failed,
            'provider_rejected',
            'Email address invalid'
        );

        $this->assertEquals(MessageDeliveryState::Failed, $message->delivery_state);
        $this->assertEquals('provider_rejected', $message->failure_reason);
        $this->assertEquals('Email address invalid', $message->failure_detail);
    }

    public function test_illegal_transition_returns_422(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $agent->grantPermission('ticket.message.view');
        $agent->grantPermission('ticket.message.send');

        $postMessage = $this->app->make(PostTicketMessage::class);
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $agent,
            channel: MessageChannel::Email,
            body: 'Test',
        );

        // Try to go directly from queued to delivered (illegal)
        $transition = $this->app->make(TransitionMessageDelivery::class);

        $this->expectException(IllegalDeliveryTransitionException::class);
        $transition->handle($message, MessageDeliveryState::Delivered);
    }

    public function test_read_receipt_rejected_for_email(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();

        $postMessage = $this->app->make(PostTicketMessage::class);
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $agent,
            channel: MessageChannel::Email,
            body: 'Test',
        );

        $transition = $this->app->make(TransitionMessageDelivery::class);
        $message = $transition->handle($message, MessageDeliveryState::Sent);
        $message = $transition->handle($message, MessageDeliveryState::Delivered);

        // Try to mark as read for email (should fail)
        $this->expectException(IllegalDeliveryTransitionException::class);
        $transition->handle($message, MessageDeliveryState::Read);
    }

    public function test_read_receipt_allowed_for_whatsapp(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();

        $postMessage = $this->app->make(PostTicketMessage::class);
        $message = $postMessage->handle(
            ticket: $ticket,
            actor: $agent,
            channel: MessageChannel::Whatsapp,
            body: 'Test',
        );

        $transition = $this->app->make(TransitionMessageDelivery::class);
        $message = $transition->handle($message, MessageDeliveryState::Sent);
        $message = $transition->handle($message, MessageDeliveryState::Delivered);
        $message = $transition->handle($message, MessageDeliveryState::Read);

        $this->assertEquals(MessageDeliveryState::Read, $message->delivery_state);
    }
}
