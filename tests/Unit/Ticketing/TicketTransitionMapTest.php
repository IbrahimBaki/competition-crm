<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Models\TicketStatus;
use App\Domains\Ticketing\Services\Lifecycle\TicketTransitionMap;
use PHPUnit\Framework\TestCase;

class TicketTransitionMapTest extends TestCase
{
    private TicketTransitionMap $map;

    protected function setUp(): void
    {
        parent::setUp();
        $this->map = new TicketTransitionMap;
    }

    public function test_new_allows_valid_transitions(): void
    {
        $this->assertTrue($this->map->allows(TicketStatus::New, TicketStatus::Open));
        $this->assertTrue($this->map->allows(TicketStatus::New, TicketStatus::Pending));
        $this->assertTrue($this->map->allows(TicketStatus::New, TicketStatus::Resolved));
        $this->assertTrue($this->map->allows(TicketStatus::New, TicketStatus::Spam));
    }

    public function test_new_rejects_invalid_transitions(): void
    {
        $this->assertFalse($this->map->allows(TicketStatus::New, TicketStatus::Closed));
    }

    public function test_closed_can_only_go_to_spam(): void
    {
        $this->assertTrue($this->map->allows(TicketStatus::Closed, TicketStatus::Spam));
        $this->assertFalse($this->map->allows(TicketStatus::Closed, TicketStatus::Open));
        $this->assertFalse($this->map->allows(TicketStatus::Closed, TicketStatus::Pending));
    }

    public function test_resolved_can_reopen(): void
    {
        $this->assertTrue($this->map->allows(TicketStatus::Resolved, TicketStatus::Open));
    }

    public function test_same_status_transition_is_allowed(): void
    {
        $this->assertTrue($this->map->allows(TicketStatus::Pending, TicketStatus::Pending));
        $this->assertTrue($this->map->allows(TicketStatus::Resolved, TicketStatus::Resolved));
    }

    public function test_spam_can_be_restored_to_open(): void
    {
        $this->assertTrue($this->map->allows(TicketStatus::Spam, TicketStatus::Open));
    }

    public function test_requires_reason_for_spam_transitions(): void
    {
        $this->assertTrue($this->map->requiresReason(TicketStatus::New, TicketStatus::Spam));
        $this->assertTrue($this->map->requiresReason(TicketStatus::Open, TicketStatus::Spam));
        $this->assertTrue($this->map->requiresReason(TicketStatus::Pending, TicketStatus::Spam));
        $this->assertTrue($this->map->requiresReason(TicketStatus::Resolved, TicketStatus::Spam));
    }

    public function test_requires_reason_for_reopen(): void
    {
        $this->assertTrue($this->map->requiresReason(TicketStatus::Resolved, TicketStatus::Open));
    }

    public function test_requires_reason_for_restore_from_spam(): void
    {
        $this->assertTrue($this->map->requiresReason(TicketStatus::Spam, TicketStatus::Open));
    }

    public function test_other_transitions_do_not_require_reason(): void
    {
        $this->assertFalse($this->map->requiresReason(TicketStatus::New, TicketStatus::Open));
        $this->assertFalse($this->map->requiresReason(TicketStatus::Open, TicketStatus::Pending));
        $this->assertFalse($this->map->requiresReason(TicketStatus::Pending, TicketStatus::Resolved));
    }

    public function test_allowed_from_returns_valid_transitions(): void
    {
        $allowed = $this->map->allowedFrom(TicketStatus::New);

        $this->assertContains(TicketStatus::Open, $allowed);
        $this->assertContains(TicketStatus::Pending, $allowed);
        $this->assertContains(TicketStatus::Resolved, $allowed);
        $this->assertContains(TicketStatus::Spam, $allowed);
    }
}
