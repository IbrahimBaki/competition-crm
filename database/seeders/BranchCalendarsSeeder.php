<?php

namespace Database\Seeders;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchWorkingHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchCalendarsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHeadquartersBranch();
        $this->seedOperations24Branch();
    }

    private function seedHeadquartersBranch(): void
    {
        $branch = Branch::firstOrCreate(
            ['code' => 'HQ'],
            [
                'id' => (string) Str::uuid(),
                'name' => [
                    'ar' => 'المقر الرئيسي',
                    'en' => 'Headquarters',
                ],
                'timezone' => 'Asia/Riyadh',
                'is_24_7' => false,
                'is_active' => true,
            ]
        );

        $this->seedWorkingHoursForStandardWeek($branch);
    }

    private function seedOperations24Branch(): void
    {
        Branch::firstOrCreate(
            ['code' => 'OPS24'],
            [
                'id' => (string) Str::uuid(),
                'name' => [
                    'ar' => 'العمليات ٢٤/٧',
                    'en' => '24/7 Operations',
                ],
                'timezone' => 'Asia/Riyadh',
                'is_24_7' => true,
                'is_active' => true,
            ]
        );
    }

    private function seedWorkingHoursForStandardWeek(Branch $branch): void
    {
        $workingDays = [0, 1, 2, 3, 4]; // Sun-Thu
        $closedDays = [5, 6]; // Fri-Sat

        foreach ($workingDays as $dayOfWeek) {
            BranchWorkingHour::firstOrCreate(
                ['branch_id' => $branch->id, 'day_of_week' => $dayOfWeek],
                [
                    'is_working' => true,
                    'opens_at' => '09:00:00',
                    'closes_at' => '17:00:00',
                ]
            );
        }

        foreach ($closedDays as $dayOfWeek) {
            BranchWorkingHour::firstOrCreate(
                ['branch_id' => $branch->id, 'day_of_week' => $dayOfWeek],
                [
                    'is_working' => false,
                    'opens_at' => null,
                    'closes_at' => null,
                ]
            );
        }
    }
}
