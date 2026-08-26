<?php

namespace Tests\Unit\Sla;

use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Services\SlaClockService;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class SlaClockServiceTest extends TestCase
{
    private SlaClockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SlaClockService::class);
    }

    public function test_position_performs_no_writes(): void
    {
        $ticket = Ticket::factory()->create();
        $clock = $ticket->slaClocks()->first();

        if (! $clock) {
            $this->markTestSkipped('No SLA clocks created for ticket');
        }

        $originalElapsed = $clock->elapsed_minutes;
        $now = CarbonImmutable::now('UTC');

        $position = $this->service->position($clock, $now);

        $clock->refresh();
        $this->assertEquals($originalElapsed, $clock->elapsed_minutes);
    }

    public function test_pause_then_resume_extends_deadline(): void
    {
        $ticket = Ticket::factory()->create();
        $clock = $ticket->slaClocks()->first();

        if (! $clock) {
            $this->markTestSkipped('No SLA clocks created for ticket');
        }

        $originalDueAt = $clock->due_at;
        $pauseTime = CarbonImmutable::now('UTC');

        $this->service->pause($clock, $pauseTime);
        $clock->refresh();
        $this->assertEquals(SlaClockState::Paused->value, $clock->state);

        $resumeTime = $pauseTime->addMinutes(30);
        $this->service->resume($clock, $resumeTime);
        $clock->refresh();

        $this->assertEquals(SlaClockState::Running->value, $clock->state);
        $this->assertTrue($clock->due_at->isAfter($originalDueAt));
    }
}
