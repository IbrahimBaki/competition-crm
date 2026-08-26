<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Models\TicketStatus;
use PHPUnit\Framework\TestCase;

class TicketStatusLifecycleTypeTest extends TestCase
{
    public function test_pending_stops_sla_clock(): void
    {
        $this->assertTrue(TicketStatus::Pending->stopsSlaClock());
    }

    public function test_resolved_stops_sla_clock(): void
    {
        $this->assertTrue(TicketStatus::Resolved->stopsSlaClock());
    }

    public function test_closed_stops_sla_clock(): void
    {
        $this->assertTrue(TicketStatus::Closed->stopsSlaClock());
    }

    public function test_spam_stops_sla_clock(): void
    {
        $this->assertTrue(TicketStatus::Spam->stopsSlaClock());
    }

    public function test_new_does_not_stop_sla_clock(): void
    {
        $this->assertFalse(TicketStatus::New->stopsSlaClock());
    }

    public function test_open_does_not_stop_sla_clock(): void
    {
        $this->assertFalse(TicketStatus::Open->stopsSlaClock());
    }

    public function test_closed_is_terminal(): void
    {
        $this->assertTrue(TicketStatus::Closed->isTerminal());
    }

    public function test_spam_is_terminal(): void
    {
        $this->assertTrue(TicketStatus::Spam->isTerminal());
    }

    public function test_new_is_not_terminal(): void
    {
        $this->assertFalse(TicketStatus::New->isTerminal());
    }

    public function test_open_is_not_terminal(): void
    {
        $this->assertFalse(TicketStatus::Open->isTerminal());
    }

    public function test_pending_is_not_terminal(): void
    {
        $this->assertFalse(TicketStatus::Pending->isTerminal());
    }

    public function test_resolved_is_not_terminal(): void
    {
        $this->assertFalse(TicketStatus::Resolved->isTerminal());
    }
}
