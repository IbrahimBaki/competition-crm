<?php

namespace Tests\Feature\Seeders;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchWorkingHour;
use Database\Seeders\BranchCalendarsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BranchCalendarsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_hq_branch_exists_with_correct_properties(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $hq = Branch::where('code', 'HQ')->firstOrFail();

        $this->assertEquals('HQ', $hq->code);
        $this->assertEquals('Headquarters', $hq->name->en);
        $this->assertEquals('المقر الرئيسي', $hq->name->ar);
        $this->assertEquals('Asia/Riyadh', $hq->timezone);
        $this->assertFalse($hq->is_24_7);
        $this->assertTrue($hq->is_active);
    }

    public function test_ops24_branch_exists_with_correct_properties(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $ops24 = Branch::where('code', 'OPS24')->firstOrFail();

        $this->assertEquals('OPS24', $ops24->code);
        $this->assertEquals('24/7 Operations', $ops24->name->en);
        $this->assertEquals('العمليات ٢٤/٧', $ops24->name->ar);
        $this->assertTrue($ops24->is_24_7);
        $this->assertTrue($ops24->is_active);
    }

    public function test_hq_has_sun_to_thu_working_hours(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $hq = Branch::where('code', 'HQ')->firstOrFail();
        $workingDays = [0, 1, 2, 3, 4]; // Sun-Thu

        foreach ($workingDays as $dayOfWeek) {
            $hour = BranchWorkingHour::where('branch_id', $hq->id)
                ->where('day_of_week', $dayOfWeek)
                ->firstOrFail();

            $this->assertTrue($hour->is_working, "Day {$dayOfWeek} should be working");
            $this->assertEquals('09:00:00', $hour->opens_at);
            $this->assertEquals('17:00:00', $hour->closes_at);
        }
    }

    public function test_hq_has_fri_sat_closed(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $hq = Branch::where('code', 'HQ')->firstOrFail();
        $closedDays = [5, 6]; // Fri-Sat

        foreach ($closedDays as $dayOfWeek) {
            $hour = BranchWorkingHour::where('branch_id', $hq->id)
                ->where('day_of_week', $dayOfWeek)
                ->firstOrFail();

            $this->assertFalse($hour->is_working, "Day {$dayOfWeek} should be closed");
            $this->assertNull($hour->opens_at);
            $this->assertNull($hour->closes_at);
        }
    }

    public function test_ops24_has_no_working_hours(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $ops24 = Branch::where('code', 'OPS24')->firstOrFail();
        $count = BranchWorkingHour::where('branch_id', $ops24->id)->count();

        $this->assertEquals(0, $count, 'OPS24 branch should have no working hour rows');
    }

    public function test_customized_working_hours_are_preserved_on_reseed(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $hq = Branch::where('code', 'HQ')->firstOrFail();
        $monday = BranchWorkingHour::where('branch_id', $hq->id)
            ->where('day_of_week', 1)
            ->firstOrFail();

        // Simulate a customized close time
        $monday->closes_at = '18:00:00';
        $monday->save();

        Artisan::call('db:seed', ['--class' => 'Database\Seeders\BranchCalendarsSeeder']);

        $monday->refresh();

        $this->assertEquals('18:00:00', $monday->closes_at, 'Customized close time should be preserved');
    }
}
