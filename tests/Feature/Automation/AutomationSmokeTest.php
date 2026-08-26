<?php

namespace Tests\Feature\Automation;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\RuleTrigger;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Ticketing\Exceptions\AutomationRuleExecutionImmutableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_routing_strategy_exists_on_department()
    {
        $department = $this->createDepartment(['routing_strategy' => 'round_robin']);
        $this->assertEquals('round_robin', $department->routing_strategy->value);
    }

    public function test_automation_rule_model_exists()
    {
        $rule = AutomationRule::create([
            'key' => 'test-rule',
            'name' => ['en' => 'Test', 'ar' => 'اختبار'],
            'trigger' => RuleTrigger::TicketCreated->value,
            'conditions' => [],
            'actions' => [],
            'is_active' => true,
        ]);

        $this->assertNotNull($rule->uuid);
        $this->assertEquals('test-rule', $rule->key);
    }

    public function test_escalate_ticket_endpoint_requires_reason()
    {
        $user = $this->createUser(['roles' => ['supervisor']]);
        $ticket = $this->createTicket();

        $response = $this->actingAs($user)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/escalate",
            ['level' => 1]
        );

        $response->assertStatus(422);
    }

    public function test_automation_permissions_exist()
    {
        $permissions = [
            'automation.rules.view',
            'automation.rules.manage',
            'automation.executions.view',
            'tickets.escalate',
        ];

        foreach ($permissions as $permission) {
            $this->assertContains($permission, PermissionKey::all());
        }
    }

    public function test_automation_rule_is_immutable()
    {
        $rule = AutomationRule::first();
        if (! $rule) {
            $this->markTestSkipped('No automation rules seeded');
        }

        $execution = $rule->executions()->first();
        if (! $execution) {
            $this->markTestSkipped('No executions found');
        }

        $this->expectException(AutomationRuleExecutionImmutableException::class);
        $execution->update(['outcome' => 'matched']);
    }
}
