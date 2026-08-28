<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Exceptions\TicketVersionConflictException;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Services\Concurrency\TicketVersionGuard;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketVersionGuardTest extends TestCase
{
    use DatabaseMigrations;

    private TicketVersionGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = app(TicketVersionGuard::class);
    }

    public function test_assert_passes_when_version_matches(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);

        // Should not throw
        $this->guard->assert($ticket, 5);
        $this->assertTrue(true);
    }

    public function test_assert_throws_when_version_mismatches(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);

        $this->expectException(TicketVersionConflictException::class);
        $this->guard->assert($ticket, 3);
    }

    public function test_assert_is_noop_when_version_is_null(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);

        // Should not throw
        $this->guard->assert($ticket, null);
        $this->assertTrue(true);
    }

    public function test_bump_increments_version_by_one(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);

        $updated = $this->guard->bump($ticket, []);

        $this->assertEquals(6, $updated->version);
    }

    public function test_bump_updates_attributes_atomically(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5, 'assigned_user_id' => null]);

        $updated = $this->guard->bump($ticket, ['assigned_user_id' => 123]);

        $this->assertEquals(6, $updated->version);
        $this->assertEquals(123, $updated->assigned_user_id);
    }

    public function test_bump_throws_on_stale_version(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);

        // Simulate another process updating it
        $ticket->updateQuietly(['version' => 6]);

        $this->expectException(TicketVersionConflictException::class);
        $this->guard->bump($ticket, [], 5);
    }

    public function test_bump_increments_unconditionally_when_version_null(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);

        $updated = $this->guard->bump($ticket, [], null);

        $this->assertEquals(6, $updated->version);
    }

    public function test_bump_conflict_exception_carries_current_state(): void
    {
        $ticket = Ticket::factory()->create(['version' => 5]);
        $ticket->updateQuietly(['version' => 6]);

        try {
            $this->guard->bump($ticket, [], 5);
            $this->fail('Expected TicketVersionConflictException');
        } catch (TicketVersionConflictException $e) {
            $this->assertEquals(6, $e->getCurrentVersion());
        }
    }
}
