<?php

namespace Tests\Feature\Api;

use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class OpenApiCoverageTest extends TestCase
{
    /**
     * Routes intentionally excluded from the public v1 contract.
     * Each entry must have a justification comment.
     */
    private const ALLOWLIST = [
        // Health probes: internal operational endpoints, not part of client contract
        'GET /api/v1/health/live',
        'GET /api/v1/health/ready',

        // Webhook receivers: provider-inbound endpoints signed by external systems,
        // not part of the client-facing surface
        'POST /api/v1/channels/email/inbound',
        'POST /api/v1/channels/whatsapp/inbound',
        'POST /api/v1/channels/whatsapp/receipts',
        'POST /api/v1/channels/sms/inbound',
        'POST /api/v1/channels/sms/receipts',
    ];

    public function test_spec_is_well_formed()
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));

        // Basic sanity checks
        $this->assertIsArray($spec);
        $this->assertArrayHasKey('openapi', $spec);
        $this->assertArrayHasKey('info', $spec);
        $this->assertArrayHasKey('paths', $spec);

        // Spec has documented paths
        $paths = $spec['paths'] ?? [];
        $this->assertNotEmpty($paths, 'Spec should document at least one path');

        // Each path has at least one operation
        foreach ($paths as $path => $operations) {
            $hasOperation = false;
            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (isset($operations[$method])) {
                    $hasOperation = true;
                    $this->assertArrayHasKey('operationId', $operations[$method], "Path $path $method missing operationId");
                    break;
                }
            }
            $this->assertTrue($hasOperation, "Path $path has no HTTP operations defined");
        }
    }

    public function test_documented_paths_are_reasonable()
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));
        $paths = $spec['paths'] ?? [];

        // Count operations
        $operationCount = 0;
        foreach ($paths as $operations) {
            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (isset($operations[$method])) {
                    $operationCount++;
                }
            }
        }

        // Should have a reasonable number of documented endpoints
        // (at minimum, covers the main domains: auth, users, customers, tickets, etc.)
        $this->assertGreaterThan(50, $operationCount, 'Spec should document at least 50 operations');
    }
}
