<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Services\TicketReferenceGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketReferenceGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private TicketReferenceGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = app(TicketReferenceGenerator::class);
    }

    public function test_allocates_reference_in_correct_format(): void
    {
        $ref = $this->generator->next();

        $this->assertMatchesRegularExpression('/^TKT-\d{4}\d{2}-\d{6}$/', $ref);
    }

    public function test_sequential_allocation_increments_sequence(): void
    {
        $ref1 = $this->generator->next();
        $ref2 = $this->generator->next();
        $ref3 = $this->generator->next();

        $this->assertStringEndsWith('-000001', $ref1);
        $this->assertStringEndsWith('-000002', $ref2);
        $this->assertStringEndsWith('-000003', $ref3);
    }

    public function test_allocates_200_distinct_references(): void
    {
        $refs = [];
        for ($i = 0; $i < 200; $i++) {
            $refs[] = $this->generator->next();
        }

        $this->assertCount(200, array_unique($refs));
    }

    public function test_never_reuses_deleted_reference(): void
    {
        $ref1 = $this->generator->next();
        $this->assertStringEndsWith('-000001', $ref1);

        $this->generator->next();
        $this->generator->next();

        $newRef = $this->generator->next();
        $this->assertStringEndsWith('-000004', $newRef);
    }

    public function test_period_rollover_restarts_sequence(): void
    {
        $ref1 = $this->generator->next(now());
        $this->assertStringEndsWith('-000001', $ref1);

        $nextMonth = now()->addMonth();
        $ref2 = $this->generator->next($nextMonth);
        $this->assertStringEndsWith('-000001', $ref2);

        $this->assertStringContainsString(now()->format('Ym'), $ref1);
        $this->assertStringContainsString($nextMonth->format('Ym'), $ref2);
    }
}
