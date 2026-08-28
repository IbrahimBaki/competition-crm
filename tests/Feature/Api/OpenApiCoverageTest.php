<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class OpenApiCoverageTest extends TestCase
{
    private const METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * Routes intentionally excluded from the documented contract.
     * Each entry must carry a justification comment.
     *
     * @var array<int, string>
     */
    private const ALLOWLIST = [
        // (empty) Health probes and provider webhooks are now documented under
        // the Observability and Channels tags respectively.
    ];

    public function test_spec_is_well_formed()
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('openapi', $spec);
        $this->assertArrayHasKey('info', $spec);
        $this->assertArrayHasKey('paths', $spec);

        $paths = $spec['paths'] ?? [];
        $this->assertNotEmpty($paths, 'Spec should document at least one path');

        foreach ($paths as $path => $operations) {
            $hasOperation = false;
            foreach (self::METHODS as $method) {
                if (isset($operations[$method])) {
                    $hasOperation = true;
                    $this->assertArrayHasKey('operationId', $operations[$method], "Path $path $method missing operationId");
                }
            }
            $this->assertTrue($hasOperation, "Path $path has no HTTP operations defined");
        }
    }

    public function test_operation_ids_are_unique()
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));

        $ids = [];
        foreach ($spec['paths'] ?? [] as $operations) {
            foreach (self::METHODS as $method) {
                if (isset($operations[$method]['operationId'])) {
                    $ids[] = $operations[$method]['operationId'];
                }
            }
        }

        $duplicates = array_keys(array_filter(array_count_values($ids), fn ($n) => $n > 1));

        $this->assertEmpty($duplicates, sprintf(
            "Duplicate operationId(s) — the generated client collapses these into one function:\n%s",
            implode("\n", $duplicates)
        ));
    }

    /**
     * The generated TypeScript client is derived from openapi.yaml, so any route
     * missing from the spec is unreachable from the frontend no matter that it
     * exists server-side. Keep both directions in lockstep.
     */
    public function test_every_route_is_documented()
    {
        $missing = array_diff($this->liveOperations(), $this->specOperations(), self::ALLOWLIST);

        $this->assertEmpty($missing, sprintf(
            "Route(s) exist in routes/api.php but are absent from docs/api/openapi.yaml.\n".
            "The generated client cannot reach them. Document them or add to ALLOWLIST with a reason:\n%s",
            implode("\n", $missing)
        ));
    }

    public function test_no_documented_operation_is_missing_a_route()
    {
        $phantom = array_diff($this->specOperations(), $this->liveOperations());

        $this->assertEmpty($phantom, sprintf(
            "Operation(s) documented in docs/api/openapi.yaml have no matching route.\n".
            "Clients will generate calls that 404:\n%s",
            implode("\n", $phantom)
        ));
    }

    /**
     * @return array<int, string> e.g. "GET /tickets/{}"
     */
    private function liveOperations(): array
    {
        $out = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            $path = '/'.preg_replace('#^api/(v1/)?#', '', $uri);

            foreach ($route->methods() as $method) {
                if (in_array(strtolower($method), self::METHODS, true)) {
                    $out[] = strtoupper($method).' '.$this->normalise($path);
                }
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @return array<int, string>
     */
    private function specOperations(): array
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));

        $out = [];
        foreach ($spec['paths'] ?? [] as $path => $operations) {
            foreach (self::METHODS as $method) {
                if (isset($operations[$method])) {
                    $out[] = strtoupper($method).' '.$this->normalise($path);
                }
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Parameter names differ between Laravel and the spec ({ticket} vs {id}),
     * so compare structure only.
     */
    private function normalise(string $path): string
    {
        return rtrim(preg_replace('/\{[^}]+\}/', '{}', $path), '/');
    }
}
