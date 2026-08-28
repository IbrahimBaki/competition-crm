<?php

namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class NoRoleNameComparisonTest extends TestCase
{
    public function test_no_role_name_comparisons_in_production_code(): void
    {
        $finder = new Finder;
        $finder->files()
            ->in(base_path('app'))
            ->name('*.php')
            ->exclude(['Security/Actions/UpdateRole.php', 'Providers/AppServiceProvider.php']);

        $violations = [];

        foreach ($finder as $file) {
            $content = $file->getContents();
            $filepath = $file->getRelativePathname();

            if (preg_match('/->name\s*([=!]=)\s*[\'"][A-Za-z_]+[\'"]/', $content)) {
                $violations[] = $filepath.': Found role name comparison';
            }

            if (strpos($content, 'hasRole(') !== false) {
                $violations[] = $filepath.': Found hasRole() call';
            }

            if (strpos($content, 'isAdmin') !== false) {
                $violations[] = $filepath.': Found isAdmin reference';
            }
        }

        $this->assertEmpty(
            $violations,
            'Found role name comparisons in production code:\n'.implode("\n", $violations)
        );
    }
}
