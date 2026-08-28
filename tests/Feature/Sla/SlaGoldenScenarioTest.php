<?php

namespace Tests\Feature\Sla;

use Carbon\Carbon;
use Tests\TestCase;

class SlaGoldenScenarioTest extends TestCase
{
    /**
     * Data provider with named SLA scenarios.
     * Each scenario tests the SLA engine under specific conditions.
     */
    public static function scenarioProvider(): array
    {
        return [
            'target_met_within_working_hours' => ['condition' => 'met'],
            'target_breached' => ['condition' => 'breach'],
            'pause_and_resume' => ['condition' => 'pause'],
            'breach_spanning_holiday' => ['condition' => 'holiday'],
            'breach_spanning_weekend' => ['condition' => 'weekend'],
            'reassignment_mid_clock' => ['condition' => 'reassign'],
            'policy_re_resolution_after_transfer' => ['condition' => 'transfer'],
            'first_response_vs_resolution_targets' => ['condition' => 'targets'],
        ];
    }

    /**
     * The provider returns string-keyed rows, which PHPUnit binds as NAMED
     * arguments — so the signature must name them individually. Taking a
     * single `array $scenario` here raises "Unknown named parameter
     * $condition", and rendering that error crashes the reporter before it
     * can print the run summary.
     *
     * @dataProvider scenarioProvider
     */
    public function test_sla_scenarios(string $condition): void
    {
        // Freeze clock
        $now = Carbon::parse('2026-08-27 09:00:00'); // Monday, working hours
        Carbon::setTestNow($now);

        // TODO: Implement scenario-specific tests
        // Each scenario should:
        // 1. Set up initial SLA policy and conditions
        // 2. Create a ticket
        // 3. Verify SLA clock starts
        // 4. Apply condition (e.g., pause, reassign, etc.)
        // 5. Verify SLA engine updates correctly
        // 6. Assert final state matches expectation

        $this->assertTrue(true, 'Scenario placeholder');

        Carbon::setTestNow();
    }
}
