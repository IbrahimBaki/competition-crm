<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Services\Conversation\DeliveryStateMachine;
use PHPUnit\Framework\TestCase;

class DeliveryStateMachineTest extends TestCase
{
    private DeliveryStateMachine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->machine = new DeliveryStateMachine;
    }

    public function test_queued_can_transition_to_sent(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Queued,
                MessageDeliveryState::Sent,
                MessageChannel::Email
            )
        );
    }

    public function test_queued_can_transition_to_failed(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Queued,
                MessageDeliveryState::Failed,
                MessageChannel::Email
            )
        );
    }

    public function test_sent_can_transition_to_delivered(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Sent,
                MessageDeliveryState::Delivered,
                MessageChannel::Email
            )
        );
    }

    public function test_sent_can_transition_to_failed(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Sent,
                MessageDeliveryState::Failed,
                MessageChannel::Email
            )
        );
    }

    public function test_delivered_can_transition_to_read(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Read,
                MessageChannel::Chat
            )
        );
    }

    public function test_delivered_can_transition_to_failed(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Failed,
                MessageChannel::Email
            )
        );
    }

    public function test_failed_can_transition_to_queued_retry(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Failed,
                MessageDeliveryState::Queued,
                MessageChannel::Email
            )
        );
    }

    public function test_read_cannot_transition(): void
    {
        $this->assertFalse(
            $this->machine->canTransition(
                MessageDeliveryState::Read,
                MessageDeliveryState::Sent,
                MessageChannel::Chat
            )
        );
    }

    public function test_read_rejected_for_email_channel(): void
    {
        $this->assertFalse(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Read,
                MessageChannel::Email
            )
        );
    }

    public function test_read_rejected_for_sms_channel(): void
    {
        $this->assertFalse(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Read,
                MessageChannel::Sms
            )
        );
    }

    public function test_read_allowed_for_whatsapp_channel(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Read,
                MessageChannel::Whatsapp
            )
        );
    }

    public function test_read_allowed_for_chat_channel(): void
    {
        $this->assertTrue(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Read,
                MessageChannel::Chat
            )
        );
    }

    public function test_illegal_transition_queued_to_delivered(): void
    {
        $this->assertFalse(
            $this->machine->canTransition(
                MessageDeliveryState::Queued,
                MessageDeliveryState::Delivered,
                MessageChannel::Email
            )
        );
    }

    public function test_illegal_transition_delivered_to_queued(): void
    {
        $this->assertFalse(
            $this->machine->canTransition(
                MessageDeliveryState::Delivered,
                MessageDeliveryState::Queued,
                MessageChannel::Email
            )
        );
    }

    public function test_allowed_from_queued(): void
    {
        $allowed = $this->machine->allowedFrom(MessageDeliveryState::Queued);
        $this->assertContains(MessageDeliveryState::Sent, $allowed);
        $this->assertContains(MessageDeliveryState::Failed, $allowed);
        $this->assertCount(2, $allowed);
    }

    public function test_allowed_from_sent(): void
    {
        $allowed = $this->machine->allowedFrom(MessageDeliveryState::Sent);
        $this->assertContains(MessageDeliveryState::Delivered, $allowed);
        $this->assertContains(MessageDeliveryState::Failed, $allowed);
    }

    public function test_allowed_from_read_is_empty(): void
    {
        $allowed = $this->machine->allowedFrom(MessageDeliveryState::Read);
        $this->assertEmpty($allowed);
    }
}
