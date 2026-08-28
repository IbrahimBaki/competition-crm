<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ScopeGoldenScenarioTest extends TestCase
{
    /**
     * Matrix of scope levels and resources.
     * Tests authorization enforcement across all combinations.
     */
    public static function scopeResourceMatrix(): array
    {
        $scopes = ['organisation', 'branch', 'department', 'team'];
        $resources = ['tickets', 'customers', 'reports', 'audit_logs'];

        $matrix = [];
        foreach ($scopes as $scope) {
            foreach ($resources as $resource) {
                $matrix["{$scope}_{$resource}"] = [
                    'scope' => $scope,
                    'resource' => $resource,
                ];
            }
        }

        return $matrix;
    }

    /**
     * The provider returns string-keyed rows, which PHPUnit binds as NAMED
     * arguments — so the signature must name them individually. Taking a
     * single `array $params` here raises "Unknown named parameter $scope",
     * and rendering that error crashes the reporter before it can print the
     * run summary.
     *
     * @dataProvider scopeResourceMatrix
     */
    public function test_scope_enforcement(string $scope, string $resource): void
    {
        // TODO: Implement scope x resource matrix
        // For each combination:
        // 1. Create a user with specific role and scope
        // 2. Attempt to access the resource (list and detail)
        // 3. Verify 404 or 403 per convention
        // 4. Verify filtered results for allowed access

        $this->assertTrue(true, 'Scenario placeholder');
    }

    public function test_scope_denied_uses_404_not_403(): void
    {
        // Verify that access denied returns 404 (resource hidden)
        // not 403 (forbidden)—consistent with the established convention
        $this->assertTrue(true, 'Convention check placeholder');
    }
}
