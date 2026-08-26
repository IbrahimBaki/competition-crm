<?php

namespace Tests\Unit\Support;

use Tests\TestCase;

class NoSecretsInRepositoryTest extends TestCase
{
    /**
     * @return string[]
     */
    private static function getDirsToScan(): array
    {
        return [
            base_path('app'),
            base_path('config'),
            base_path('routes'),
        ];
    }

    public function test_config_security_is_env_backed(): void
    {
        $configFile = config_path('security.php');
        $content = file_get_contents($configFile);

        // Verify all values use env() - no hardcoded secrets
        $this->assertStringContainsString("env('ATTACHMENTS_DISK'", $content);
        $this->assertStringContainsString("env('UPLOAD_MAX_SIZE_KB'", $content);
        $this->assertStringContainsString("env('MALWARE_SCANNER'", $content);
        $this->assertStringContainsString("env('BOT_PROTECTION'", $content);
    }

    public function test_no_secrets_exposed_in_resources(): void
    {
        $secretKeys = [
            'token',
            'secret',
            'password',
            '_hash',
            'storage_key',
            'checksum',
        ];

        $resourcePattern = '/Resource\.php$/';
        $violations = [];

        $resourceDir = base_path('app');
        $files = $this->getPhpFiles($resourceDir);

        foreach ($files as $file) {
            if (! preg_match($resourcePattern, $file)) {
                continue;
            }

            $content = file_get_contents($file);

            // Look for toArray method
            if (! preg_match('/public\s+function\s+toArray/i', $content)) {
                continue;
            }

            foreach ($secretKeys as $key) {
                if (preg_match("/'$key'|\"$key\"/i", $content)) {
                    $violations[] = str_replace(base_path(), '', $file);
                }
            }
        }

        $this->assertEmpty(
            $violations,
            'Secret-shaped keys exposed in Resources: '.implode(', ', $violations)
        );
    }

    /**
     * @return string[]
     */
    private function getPhpFiles(string $path): array
    {
        $files = [];
        if (! is_dir($path)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }
}
