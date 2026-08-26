<?php

namespace Database\Seeders;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Sla\Models\SlaPolicy;
use App\Domains\Sla\Models\SlaTarget;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Ticketing\Models\TicketPriority;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        $branch = Branch::first();

        if (! $branch) {
            $this->command?->warn('No branch found; skipping default SLA policy.');

            return;
        }

        $policy = SlaPolicy::updateOrCreate(
            ['uuid' => 'default-sla-policy-v1'],
            [
                'branch_id' => $branch->id,
                'name' => [
                    'en' => 'Default SLA Policy',
                    'ar' => 'سياسة SLA الافتراضية',
                ],
                'is_default' => true,
                'is_active' => true,
                'warning_threshold_percent' => 80,
            ]
        );

        $targets = [
            [
                'type' => SlaTargetType::FirstResponse->value,
                'priority' => TicketPriority::Low->value,
                'minutes' => 480, // 8 hours
            ],
            [
                'type' => SlaTargetType::FirstResponse->value,
                'priority' => TicketPriority::Normal->value,
                'minutes' => 240, // 4 hours
            ],
            [
                'type' => SlaTargetType::FirstResponse->value,
                'priority' => TicketPriority::High->value,
                'minutes' => 120, // 2 hours
            ],
            [
                'type' => SlaTargetType::FirstResponse->value,
                'priority' => TicketPriority::Urgent->value,
                'minutes' => 60, // 1 hour
            ],
            [
                'type' => SlaTargetType::Resolution->value,
                'priority' => TicketPriority::Low->value,
                'minutes' => 2880, // 2 days
            ],
            [
                'type' => SlaTargetType::Resolution->value,
                'priority' => TicketPriority::Normal->value,
                'minutes' => 1440, // 1 day
            ],
            [
                'type' => SlaTargetType::Resolution->value,
                'priority' => TicketPriority::High->value,
                'minutes' => 480, // 8 hours
            ],
            [
                'type' => SlaTargetType::Resolution->value,
                'priority' => TicketPriority::Urgent->value,
                'minutes' => 240, // 4 hours
            ],
        ];

        foreach ($targets as $idx => $target) {
            $existing = SlaTarget::where('sla_policy_id', $policy->id)
                ->where('target_type', $target['type'])
                ->where('priority', $target['priority'])
                ->whereNull('ticket_category_id')
                ->whereNull('service_tier')
                ->first();

            if (! $existing) {
                SlaTarget::create([
                    'uuid' => Str::uuid(),
                    'sla_policy_id' => $policy->id,
                    'target_type' => $target['type'],
                    'priority' => $target['priority'],
                    'ticket_category_id' => null,
                    'service_tier' => null,
                    'minutes' => $target['minutes'],
                ]);
            }
        }
    }
}
