<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Services\Lifecycle\ReopenWindow;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReopenWindowTest extends TestCase
{
    private ReopenWindow $window;

    protected function setUp(): void
    {
        parent::setUp();
        $this->window = new ReopenWindow;
    }

    public function test_deadline_is_set_correctly(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', 14);

        $resolved = Carbon::parse('2026-08-26 12:00:00');
        $deadline = $this->window->deadlineFor($resolved);

        $this->assertEquals('2026-09-09 12:00:00', $deadline->toDateTimeString());
    }

    public function test_zero_days_returns_null_deadline(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', 0);

        $resolved = Carbon::parse('2026-08-26 12:00:00');
        $deadline = $this->window->deadlineFor($resolved);

        $this->assertNull($deadline);
    }

    public function test_negative_days_treated_as_zero(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', -5);

        $resolved = Carbon::parse('2026-08-26 12:00:00');
        $deadline = $this->window->deadlineFor($resolved);

        $this->assertNull($deadline);
    }

    public function test_is_open_inside_window(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', 14);

        $ticket = $this->createTicketWithResolvedAt('2026-08-26 12:00:00', '2026-09-09 12:00:00');
        $now = Carbon::parse('2026-09-01 00:00:00');

        $this->assertTrue($this->window->isOpen($ticket, $now));
    }

    public function test_is_open_exactly_at_deadline(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', 14);

        $ticket = $this->createTicketWithResolvedAt('2026-08-26 12:00:00', '2026-09-09 12:00:00');
        $now = Carbon::parse('2026-09-09 12:00:00');

        $this->assertTrue($this->window->isOpen($ticket, $now));
    }

    public function test_is_not_open_after_deadline(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', 14);

        $ticket = $this->createTicketWithResolvedAt('2026-08-26 12:00:00', '2026-09-09 12:00:00');
        $now = Carbon::parse('2026-09-09 12:00:01');

        $this->assertFalse($this->window->isOpen($ticket, $now));
    }

    public function test_is_not_open_with_null_deadline(): void
    {
        $this->app['config']->set('tickets.reopen_window_days', 0);

        $ticket = $this->createTicketWithResolvedAt('2026-08-26 12:00:00', null);

        $this->assertFalse($this->window->isOpen($ticket, now()));
    }

    private function createTicketWithResolvedAt(string $resolvedAt, ?string $deadlineAt)
    {
        return Ticket::factory()->make([
            'resolved_at' => Carbon::parse($resolvedAt),
            'reopen_deadline_at' => $deadlineAt ? Carbon::parse($deadlineAt) : null,
        ]);
    }
}
