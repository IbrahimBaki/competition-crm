<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds default SLA policies and their priority targets.
 *
 * Wire-up point for future story that creates sla_policies and sla_targets tables.
 * When tables exist, this seeder will install:
 * - Default SLA policy with bilingual name
 * - Priority-based target times (low, medium, high, urgent)
 *
 * @codeCoverageIgnore
 */
class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('sla_policies')) {
            $this->command?->warn('SLA tables not yet migrated; skipping default SLA policy.');

            return;
        }
    }
}
