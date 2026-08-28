<?php

namespace Database\Seeders;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Models\RuleTrigger;
use Illuminate\Database\Seeder;

final class AutomationRulesSeeder extends Seeder
{
    public function run(): void
    {
        AutomationRule::updateOrCreate(
            ['key' => 'approaching-breach-escalate'],
            [
                'name' => [
                    'en' => 'Escalate on Approaching Breach',
                    'ar' => 'ترقية عند الاقتراب من انتهاك SLA',
                ],
                'trigger' => RuleTrigger::SlaWarningRaised->value,
                'priority' => 10,
                'is_active' => true,
                'conditions' => [],
                'actions' => [
                    [
                        'type' => RuleActionType::Escalate->value,
                        'level' => 1,
                        'reason' => 'Automatic escalation on SLA warning',
                    ],
                ],
            ]
        );

        AutomationRule::updateOrCreate(
            ['key' => 'stale-ticket-nudge'],
            [
                'name' => [
                    'en' => 'Nudge on Stale Ticket',
                    'ar' => 'إشعار بالتذكرة المعطلة',
                ],
                'trigger' => RuleTrigger::Scheduled->value,
                'priority' => 20,
                'is_active' => true,
                'cooldown_minutes' => 60,
                'conditions' => [
                    [
                        'field' => 'minutes_since_last_agent_message',
                        'operator' => 'gt',
                        'value' => 1440,
                    ],
                ],
                'actions' => [
                    [
                        'type' => RuleActionType::Notify->value,
                        'channel' => 'internal',
                        'recipient' => 'assigned_agent',
                    ],
                ],
            ]
        );

        AutomationRule::updateOrCreate(
            ['key' => 'auto-close-after-inactivity'],
            [
                'name' => [
                    'en' => 'Auto-close After Inactivity',
                    'ar' => 'إغلاق تلقائي بعد الخمول',
                ],
                'trigger' => RuleTrigger::Scheduled->value,
                'priority' => 30,
                'is_active' => true,
                'cooldown_minutes' => 1440,
                'conditions' => [
                    [
                        'field' => 'minutes_since_last_customer_message',
                        'operator' => 'gt',
                        'value' => 10080,
                    ],
                ],
                'actions' => [
                    [
                        'type' => RuleActionType::ChangeStatus->value,
                        'status' => 'closed',
                    ],
                ],
            ]
        );
    }
}
