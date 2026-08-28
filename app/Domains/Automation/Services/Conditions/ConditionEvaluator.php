<?php

namespace App\Domains\Automation\Services\Conditions;

use App\Domains\Automation\Exceptions\UnsupportedRuleConditionException;

final class ConditionEvaluator
{
    /**
     * @return array{matched: bool, snapshot: array<int, array<string, mixed>>}
     */
    public function evaluate(array $conditions, array $facts): array
    {
        if (empty($conditions)) {
            return ['matched' => true, 'snapshot' => []];
        }

        $snapshot = [];
        $matched = $this->evaluateNode($conditions, $facts, $snapshot);

        return ['matched' => $matched, 'snapshot' => $snapshot];
    }

    private function evaluateNode(mixed $node, array $facts, array &$snapshot): bool
    {
        if (is_array($node)) {
            if (isset($node['all'])) {
                return $this->evaluateAll($node['all'], $facts, $snapshot);
            }
            if (isset($node['any'])) {
                return $this->evaluateAny($node['any'], $facts, $snapshot);
            }
            if (isset($node['field'])) {
                return $this->evaluateLeaf($node, $facts, $snapshot);
            }
        }

        return true;
    }

    private function evaluateAll(array $conditions, array $facts, array &$snapshot): bool
    {
        foreach ($conditions as $condition) {
            if (! $this->evaluateNode($condition, $facts, $snapshot)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateAny(array $conditions, array $facts, array &$snapshot): bool
    {
        foreach ($conditions as $condition) {
            if ($this->evaluateNode($condition, $facts, $snapshot)) {
                return true;
            }
        }

        return false;
    }

    private function evaluateLeaf(array $condition, array $facts, array &$snapshot): bool
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        $actual = $facts[$field] ?? null;

        $result = match ($operator) {
            'eq' => $actual === $value,
            'neq' => $actual !== $value,
            'in' => is_array($value) && in_array($actual, $value, true),
            'not_in' => ! (is_array($value) && in_array($actual, $value, true)),
            'gt' => $actual > $value,
            'gte' => $actual >= $value,
            'lt' => $actual < $value,
            'lte' => $actual <= $value,
            'is_null' => $actual === null,
            'is_not_null' => $actual !== null,
            default => throw new UnsupportedRuleConditionException("Unsupported operator: {$operator}"),
        };

        $snapshot[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'actual' => $actual,
            'matched' => $result,
        ];

        return $result;
    }
}
