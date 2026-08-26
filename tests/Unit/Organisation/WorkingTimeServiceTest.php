<?php

namespace Tests\Unit\Organisation;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Services\WorkingTimeService;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkingTimeServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkingTimeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkingTimeService::class);
    }

    public function test_24_7_fast_path(): void
    {
        $branch = Branch::create([
            'id' => Str::uuid(),
            'name' => ['ar' => 'فرع', 'en' => 'Branch'],
            'code' => 'test',
            'timezone' => 'UTC',
            'is_24_7' => true,
        ]);

        $from = new DateTimeImmutable('2026-08-26 10:00:00');
        $to = new DateTimeImmutable('2026-08-26 12:30:00');

        // Straight wall-clock: 150 minutes
        $elapsed = $this->service->elapsedWorkingMinutes($branch, $from, $to);
        $this->assertEquals(150, $elapsed);
    }

    public function test_to_before_from_returns_zero(): void
    {
        $branch = Branch::create([
            'id' => Str::uuid(),
            'name' => ['ar' => 'فرع', 'en' => 'Branch'],
            'code' => 'test',
            'timezone' => 'UTC',
            'is_24_7' => false,
        ]);

        $from = new DateTimeImmutable('2026-08-26 12:00:00');
        $to = new DateTimeImmutable('2026-08-26 10:00:00');

        $elapsed = $this->service->elapsedWorkingMinutes($branch, $from, $to);
        $this->assertEquals(0, $elapsed);
    }

    public function test_same_instant_returns_zero(): void
    {
        $branch = Branch::create([
            'id' => Str::uuid(),
            'name' => ['ar' => 'فرع', 'en' => 'Branch'],
            'code' => 'test',
            'timezone' => 'UTC',
            'is_24_7' => false,
        ]);

        $instant = new DateTimeImmutable('2026-08-26 12:00:00');
        $elapsed = $this->service->elapsedWorkingMinutes($branch, $instant, $instant);
        $this->assertEquals(0, $elapsed);
    }

    public function test_add_working_minutes_inverse_of_elapsed(): void
    {
        $branch = Branch::create([
            'id' => Str::uuid(),
            'name' => ['ar' => 'فرع', 'en' => 'Branch'],
            'code' => 'test',
            'timezone' => 'UTC',
            'is_24_7' => true,
        ]);

        $start = new DateTimeImmutable('2026-08-26 10:00:00');
        $added = $this->service->addWorkingMinutes($branch, $start, 150);

        // Should be equivalent to start + 150 minutes
        $expected = $start->modify('+150 minutes');
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $added->format('Y-m-d H:i:s'));
    }
}
