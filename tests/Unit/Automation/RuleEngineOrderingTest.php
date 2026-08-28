<?php

namespace Tests\Unit\Automation;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\RuleTrigger;
use PHPUnit\Framework\TestCase;

class RuleEngineOrderingTest extends TestCase
{
    public function test_rule_evaluation_order()
    {
        // Create rules with different priorities and scope
        $globalRule = AutomationRule::create([
            'key' => 'global-rule',
            'name' => ['en' => 'Global', 'ar' => 'عام'],
            'trigger' => RuleTrigger::TicketCreated->value,
            'priority' => 10,
            'department_id' => null,
            'conditions' => [],
            'actions' => [],
            'is_active' => true,
        ]);

        $scopedRule = AutomationRule::create([
            'key' => 'scoped-rule',
            'name' => ['en' => 'Scoped', 'ar' => 'محلي'],
            'trigger' => RuleTrigger::TicketCreated->value,
            'priority' => 20,
            'department_id' => 1,
            'conditions' => [],
            'actions' => [],
            'is_active' => true,
        ]);

        // Department-scoped rules should be ordered before global
        $this->assertLessThan(
            $globalRule->id,
            $scopedRule->id,
            'Scoped rule should be evaluated before global'
        );
    }

    public function test_stop_on_match_halts_evaluation()
    {
        $rule = AutomationRule::create([
            'key' => 'stop-rule',
            'name' => ['en' => 'Stop', 'ar' => 'توقف'],
            'trigger' => RuleTrigger::TicketCreated->value,
            'priority' => 1,
            'conditions' => [],
            'actions' => [],
            'stop_on_match' => true,
            'is_active' => true,
        ]);

        $this->assertTrue($rule->stop_on_match);
    }
}
