<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Domains\Reporting\Services\Definitions\ReportRegistry;
use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_report_totals_match_ticket_sla_resource(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = CarbonImmutable::now('UTC');
        $from = $now->subDays(7);
        $to = $now;

        // Create ticket with SLA clocks
        $ticket = Ticket::factory()->create([
            'created_at' => $from->addDay(),
        ]);

        // Create met FirstResponse clock
        $ticket->slaClocks()->create([
            'sla_policy_id' => 1,
            'sla_target_id' => 1,
            'target_type' => SlaTargetType::FirstResponse->value,
            'target_minutes' => 60,
            'started_at' => $from->addDay(),
            'elapsed_minutes' => 45,
            'last_counted_at' => $from->addDay()->addMinutes(45),
            'due_at' => $from->addDay()->addMinutes(60),
            'state' => SlaClockState::Met->value,
            'completed_at' => $from->addDay()->addMinutes(45),
        ]);

        // Create breached Resolution clock
        $ticket->slaClocks()->create([
            'sla_policy_id' => 1,
            'sla_target_id' => 2,
            'target_type' => SlaTargetType::Resolution->value,
            'target_minutes' => 480,
            'started_at' => $from->addDay(),
            'elapsed_minutes' => 600,
            'last_counted_at' => $from->addDay()->addMinutes(600),
            'due_at' => $from->addDay()->addMinutes(480),
            'state' => SlaClockState::Breached->value,
            'breached_at' => $from->addDay()->addMinutes(480),
        ]);

        // Query report
        $registry = app(ReportRegistry::class);
        $slaReport = $registry->resolve('sla_performance');

        $filter = new ReportFilter(
            from: $from->startOfDay(),
            to: $to->endOfDay(),
            timezone: 'UTC',
        );

        $result = $slaReport->build($filter, fn ($q) => $q);

        // Verify: 2 clocks total (1 met, 1 breached)
        $this->assertCount(2, $result->rows);

        $firstResponse = $result->rows[0];
        $this->assertEquals(SlaTargetType::FirstResponse->value, $firstResponse['target_type']);
        $this->assertEquals(1, $firstResponse['total']);
        $this->assertEquals(1, $firstResponse['met']);
        $this->assertEquals(0, $firstResponse['breached']);
        $this->assertEquals(45, $firstResponse['avg_elapsed']);

        $resolution = $result->rows[1];
        $this->assertEquals(SlaTargetType::Resolution->value, $resolution['target_type']);
        $this->assertEquals(1, $resolution['total']);
        $this->assertEquals(0, $resolution['met']);
        $this->assertEquals(1, $resolution['breached']);
        $this->assertEquals(600, $resolution['avg_elapsed']);

        // Verify totals
        $this->assertEquals(2, $result->totals['total']);
        $this->assertEquals(1, $result->totals['met']);
        $this->assertEquals(1, $result->totals['breached']);
        $this->assertEquals(50.0, $result->totals['attainment_pct']); // 1 met / 2 total
    }
}
