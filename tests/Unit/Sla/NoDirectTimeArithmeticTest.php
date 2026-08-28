<?php

namespace Tests\Unit\Sla;

use PHPUnit\Framework\TestCase;

class NoDirectTimeArithmeticTest extends TestCase
{
    public function test_sla_services_do_not_use_direct_time_arithmetic(): void
    {
        $slaDir = dirname(__DIR__, 3).'/app/Domains/Sla';

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($slaDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $forbidden = [
            'diffInMinutes',
            'diffInHours',
            'diffInDays',
            'diffInSeconds',
            'addMinutes(',
            'addHours(',
            'subMinutes(',
            'Carbon::now()',
            '->now()',
        ];

        $allowlist = [
            'workingTime',
            'WorkingTimeService',
        ];

        $violations = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            foreach ($forbidden as $pattern) {
                if (strpos($content, $pattern) === false) {
                    continue;
                }

                $isAllowed = false;
                foreach ($allowlist as $allowedContext) {
                    if (strpos($content, $allowedContext) !== false) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (! $isAllowed) {
                    $violations[] = sprintf(
                        'File %s uses forbidden pattern "%s" outside WorkingTimeService context',
                        str_replace($slaDir, 'app/Domains/Sla', $file->getPathname()),
                        $pattern
                    );
                }
            }
        }

        $this->assertEmpty($violations, implode("\n", $violations));
    }
}
