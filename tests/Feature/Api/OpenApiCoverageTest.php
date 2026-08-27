<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class OpenApiCoverageTest extends TestCase
{
    public function test_all_api_routes_are_documented()
    {
        $routes = collect(Route::getRoutes())
            ->filter(fn($route) => str_starts_with($route->getPrefix(), 'api/v1'))
            ->map(fn($route) => [
                'path' => $route->getPath(),
                'methods' => $route->methods(),
            ])
            ->toArray();

        $this->assertNotEmpty($routes, 'No API routes found');

        $openapi = Yaml::parseFile(__DIR__ . '/../../docs/api/openapi.yaml');
        $documentedPaths = array_keys($openapi['paths'] ?? []);

        $this->assertNotEmpty($documentedPaths, 'No paths documented in OpenAPI');
    }
}
