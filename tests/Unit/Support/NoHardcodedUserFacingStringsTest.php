<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;

class NoHardcodedUserFacingStringsTest extends TestCase
{
    public function test_no_hardcoded_exception_messages_in_exceptions(): void
    {
        $basePath = base_path('app/Domains');
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (! str_ends_with($file->getPathname(), 'Exception.php')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            // Check for throw statements with literal strings
            if (preg_match_all('/throw new \w+Exception\("([^"]{4,})"/', $content, $matches)) {
                foreach ($matches[1] as $message) {
                    // Exclude internal validation/logic exceptions
                    if (! str_contains($file->getPathname(), '/Exceptions/')) {
                        continue;
                    }

                    $violations[] = sprintf(
                        '%s: hardcoded message "%s"',
                        $file->getPathname(),
                        substr($message, 0, 50)
                    );
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Found hardcoded exception messages:\n".implode("\n", $violations)
        );
    }
}
