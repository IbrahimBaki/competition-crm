<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;

class ApiConventionsAuditTest extends TestCase
{
    public function test_no_unpaginated_list_endpoints(): void
    {
        $controllers = $this->getControllerFiles();
        $violations = [];

        foreach ($controllers as $path) {
            $violations = array_merge($violations, $this->checkController($path));
        }

        $this->assertEmpty(
            $violations,
            "Found unpaginated list endpoints:\n".implode("\n", $violations)
        );
    }

    private function getControllerFiles(): array
    {
        $controllers = [];
        $basePath = base_path('app/Domains');

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (str_ends_with($file->getPathname(), 'Controller.php')) {
                $controllers[] = $file->getPathname();
            }
        }

        return $controllers;
    }

    private function checkController(string $path): array
    {
        $violations = [];
        $content = file_get_contents($path);

        // Find all public function index() methods
        if (preg_match('/public\s+function\s+index\s*\(/', $content)) {
            // Check if the method body contains ->get() without pagination
            if (preg_match('/public\s+function\s+index\s*\([^)]*\)\s*\{[^}]*->get\s*\(\s*\)/', $content)) {
                $violations[] = $path.': index() returns unpaginated ->get()';
            }
        }

        return $violations;
    }
}
