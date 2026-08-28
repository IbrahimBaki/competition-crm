<?php

namespace Tests\Feature\Sla;

use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Sla\Services\SlaClockService;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class SlaIntegrationTest extends TestCase
{
    public function test_sla_clocks_are_created_when_ticket_is_created(): void
    {
        $ticket = Ticket::factory()->create();

        $clocks = $ticket->slaClocks()->get();

        $this->assertCount(2, $clocks);
        $this->assertTrue($clocks->contains(function ($c) {
            return $c->target_type === SlaTargetType::FirstResponse->value;
        }));
        $this->assertTrue($clocks->contains(function ($c) {
            return $c->target_type === SlaTargetType::Resolution->value;
        }));
    }

    public function test_sla_clock_state_transitions_correctly(): void
    {
        $clockService = app(SlaClockService::class);
        $ticket = Ticket::factory()->create();
        $clock = $ticket->slaClocks()->first();

        $this->assertEquals(SlaClockState::Running->value, $clock->state);

        $now = CarbonImmutable::now('UTC');
        $clockService->pause($clock, $now);
        $clock->refresh();

        $this->assertEquals(SlaClockState::Paused->value, $clock->state);

        $clockService->resume($clock, $now->addHours(1));
        $clock->refresh();

        $this->assertEquals(SlaClockState::Running->value, $clock->state);
    }

    public function test_pause_interval_is_recorded(): void
    {
        $clockService = app(SlaClockService::class);
        $ticket = Ticket::factory()->create();
        $clock = $ticket->slaClocks()->first();

        $now = CarbonImmutable::now('UTC');
        $clockService->pause($clock, $now);

        $interval = $clock->pauseIntervals()->whereNull('resumed_at')->first();

        $this->assertNotNull($interval);
        $this->assertTrue($interval->paused_at->equalTo($now));
        $this->assertNull($interval->resumed_at);
    }

    public function test_clock_position_calculates_correctly(): void
    {
        $clockService = app(SlaClockService::class);
        $ticket = Ticket::factory()->create();
        $clock = $ticket->slaClocks()->first();

        $position = $clockService->position($clock, CarbonImmutable::now('UTC'));

        $this->assertEquals(SlaTargetType::FirstResponse->value, $position->targetType->value);
        $this->assertEquals(SlaClockState::Running->value, $position->state->value);
        $this->assertNotNull($position->dueAt);
        $this->assertGreaterThan(0, $position->targetMinutes);
        $this->assertGreaterThanOrEqual(0, $position->elapsedMinutes);
    }
}
