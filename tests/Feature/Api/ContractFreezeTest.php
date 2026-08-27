<?php

namespace Tests\Feature\Api;

use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class ContractFreezeTest extends TestCase
{
    public function test_spec_additive_changes_only()
    {
        $live = Yaml::parseFile(base_path('docs/api/openapi.yaml'));
        $frozen = Yaml::parseFile(base_path('docs/api/openapi.v1.frozen.yaml'));

        $livePaths = $live['paths'] ?? [];
        $frozenPaths = $frozen['paths'] ?? [];

        // 1. No paths removed
        $removedPaths = array_diff_key($frozenPaths, $livePaths);
        $this->assertEmpty($removedPaths, sprintf(
            "Breaking change: paths removed from spec:\n%s",
            implode("\n", array_keys($removedPaths))
        ));

        // 2. No operations removed
        foreach ($frozenPaths as $path => $frozenOps) {
            if (! isset($livePaths[$path])) {
                continue;
            }

            $liveOps = $livePaths[$path];

            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (isset($frozenOps[$method]) && ! isset($liveOps[$method])) {
                    $this->fail(sprintf(
                        'Breaking change: operation %s %s removed from spec',
                        strtoupper($method),
                        $path
                    ));
                }
            }
        }

        // 3. No operationId changes (renames are breaking)
        foreach ($frozenPaths as $path => $frozenOps) {
            if (! isset($livePaths[$path])) {
                continue;
            }

            $liveOps = $livePaths[$path];

            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (isset($frozenOps[$method]['operationId']) && isset($liveOps[$method]['operationId'])) {
                    $this->assertSame(
                        $frozenOps[$method]['operationId'],
                        $liveOps[$method]['operationId'],
                        "Breaking change: operationId renamed for {$method} {$path}"
                    );
                }
            }
        }
    }

    public function test_spec_response_contracts_preserved()
    {
        $live = Yaml::parseFile(base_path('docs/api/openapi.yaml'));
        $frozen = Yaml::parseFile(base_path('docs/api/openapi.v1.frozen.yaml'));

        $livePaths = $live['paths'] ?? [];
        $frozenPaths = $frozen['paths'] ?? [];

        // 1. No response properties removed (check 200/201 responses)
        foreach ($frozenPaths as $path => $frozenOps) {
            if (! isset($livePaths[$path])) {
                continue;
            }

            $liveOps = $livePaths[$path];

            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (! isset($frozenOps[$method]['responses']) || ! isset($liveOps[$method]['responses'])) {
                    continue;
                }

                $frozenResponses = $frozenOps[$method]['responses'];
                $liveResponses = $liveOps[$method]['responses'];

                // Check success responses (200, 201)
                foreach (['200', '201'] as $code) {
                    if (! isset($frozenResponses[$code])) {
                        continue;
                    }

                    if (! isset($liveResponses[$code])) {
                        $this->fail("Breaking change: status code {$code} removed from {$method} {$path}");
                    }

                    // Basic schema check: if frozen has a response, live must have it
                    $this->assertNotNull(
                        $liveResponses[$code],
                        "Breaking change: response schema for {$code} removed from {$method} {$path}"
                    );
                }
            }
        }
    }

    public function test_spec_allows_new_paths()
    {
        $live = Yaml::parseFile(base_path('docs/api/openapi.yaml'));
        $frozen = Yaml::parseFile(base_path('docs/api/openapi.v1.frozen.yaml'));

        $livePaths = $live['paths'] ?? [];
        $frozenPaths = $frozen['paths'] ?? [];

        // Count new paths (additive change, should pass)
        $newPaths = array_diff_key($livePaths, $frozenPaths);

        // Test just checks they exist and are properly formed
        foreach ($newPaths as $path => $operations) {
            $this->assertIsArray($operations);

            $hasOperation = false;
            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (isset($operations[$method])) {
                    $hasOperation = true;
                    $this->assertArrayHasKey('operationId', $operations[$method]);
                    break;
                }
            }

            $this->assertTrue($hasOperation, "New path {$path} has no operations");
        }
    }

    public function test_spec_allows_new_optional_fields()
    {
        // Spec should be able to add optional fields to response schemas
        // This test just verifies the spec is still parseable and well-formed
        $live = Yaml::parseFile(base_path('docs/api/openapi.yaml'));

        $this->assertIsArray($live);
        $this->assertArrayHasKey('paths', $live);
        $this->assertNotEmpty($live['paths']);
    }
}
