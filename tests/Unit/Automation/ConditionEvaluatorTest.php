<?php

namespace Tests\Unit\Automation;

use App\Domains\Automation\Exceptions\UnsupportedRuleConditionException;
use App\Domains\Automation\Services\Conditions\ConditionEvaluator;
use PHPUnit\Framework\TestCase;

class ConditionEvaluatorTest extends TestCase
{
    private ConditionEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new ConditionEvaluator;
    }

    public function test_empty_conditions_match()
    {
        $result = $this->evaluator->evaluate([], []);
        $this->assertTrue($result['matched']);
    }

    public function test_eq_operator()
    {
        $result = $this->evaluator->evaluate(
            ['field' => 'status', 'operator' => 'eq', 'value' => 'open'],
            ['status' => 'open']
        );
        $this->assertTrue($result['matched']);
    }

    public function test_in_operator()
    {
        $result = $this->evaluator->evaluate(
            ['field' => 'priority', 'operator' => 'in', 'value' => ['high', 'urgent']],
            ['priority' => 'urgent']
        );
        $this->assertTrue($result['matched']);
    }

    public function test_gt_operator()
    {
        $result = $this->evaluator->evaluate(
            ['field' => 'age_business_minutes', 'operator' => 'gt', 'value' => 100],
            ['age_business_minutes' => 150]
        );
        $this->assertTrue($result['matched']);
    }

    public function test_all_group()
    {
        $result = $this->evaluator->evaluate(
            [
                'all' => [
                    ['field' => 'status', 'operator' => 'eq', 'value' => 'open'],
                    ['field' => 'priority', 'operator' => 'eq', 'value' => 'high'],
                ],
            ],
            ['status' => 'open', 'priority' => 'high']
        );
        $this->assertTrue($result['matched']);
    }

    public function test_any_group()
    {
        $result = $this->evaluator->evaluate(
            [
                'any' => [
                    ['field' => 'status', 'operator' => 'eq', 'value' => 'closed'],
                    ['field' => 'priority', 'operator' => 'eq', 'value' => 'high'],
                ],
            ],
            ['status' => 'open', 'priority' => 'high']
        );
        $this->assertTrue($result['matched']);
    }

    public function test_unsupported_operator_throws()
    {
        $this->expectException(UnsupportedRuleConditionException::class);
        $this->evaluator->evaluate(
            ['field' => 'status', 'operator' => 'invalid_op', 'value' => 'open'],
            ['status' => 'open']
        );
    }

    public function test_snapshot_contains_evaluation_results()
    {
        $result = $this->evaluator->evaluate(
            ['field' => 'status', 'operator' => 'eq', 'value' => 'open'],
            ['status' => 'open']
        );

        $this->assertNotEmpty($result['snapshot']);
        $this->assertEquals('status', $result['snapshot'][0]['field']);
        $this->assertEquals('eq', $result['snapshot'][0]['operator']);
        $this->assertTrue($result['snapshot'][0]['matched']);
    }
}
