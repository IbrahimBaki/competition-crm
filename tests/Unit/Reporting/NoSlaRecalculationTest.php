<?php

declare(strict_types=1);

namespace Tests\Unit\Reporting;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\TestCase;

class NoSlaRecalculationTest extends TestCase
{
    public function test_reporting_domain_does_not_reference_working_time_service(): void
    {
        $this->assertNoPatternInDirectory(
            'app/Domains/Reporting/',
            ['WorkingTimeService', 'diffIn', '->diff(', 'SlaEvaluator'],
            'Reporting domain must not recalculate SLA times. Read persisted columns only.'
        );
    }

    private function assertNoPatternInDirectory(string $directory, array $patterns, string $message): void
    {
        $files = File::allFiles($directory);
        $violations = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = $file->getContents();

            foreach ($patterns as $pattern) {
                if (str_contains($content, $pattern)) {
                    $violations[] = sprintf(
                        "%s contains '%s'",
                        $file->getRelativePath().'/'.$file->getFilename(),
                        $pattern
                    );
                }
            }
        }

        $this->assertEmpty(
            $violations,
            $message."\n".implode("\n", $violations)
        );
    }
}
