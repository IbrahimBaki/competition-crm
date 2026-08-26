<?php

namespace Tests\Unit\Support;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Tests\TestCase;

class MassAssignmentAuditTest extends TestCase
{
    public function test_all_eloquent_models_have_mass_assignment_protection(): void
    {
        $appPath = app_path();
        $models = $this->findModels($appPath);

        $violations = [];
        foreach ($models as $modelClass) {
            try {
                $reflection = new ReflectionClass($modelClass);
                $guarded = $reflection->getProperty('guarded');
                $guarded->setAccessible(true);
                $guardedValue = $guarded->getDefaultValue();

                $fillable = $reflection->getProperty('fillable');
                $fillable->setAccessible(true);
                $fillableValue = $fillable->getDefaultValue();

                $hasGuarded = $guardedValue !== null && (
                    $guardedValue === ['*'] ||
                    (is_array($guardedValue) && ! empty($guardedValue))
                );

                $hasFillable = $fillableValue !== null && is_array($fillableValue) && ! empty($fillableValue);

                if (! $hasGuarded && ! $hasFillable) {
                    $violations[] = $modelClass;
                }
            } catch (\ReflectionException) {
                // Skip if can't reflect
            }
        }

        $this->assertEmpty(
            $violations,
            'Models missing mass-assignment protection: '.implode(', ', $violations)
        );
    }

    /**
     * @return class-string<Model>[]
     */
    private function findModels(string $path): array
    {
        $models = [];
        $files = $this->getPhpFiles($path);

        foreach ($files as $file) {
            $class = $this->getClassFromFile($file);
            if ($class && is_subclass_of($class, Model::class)) {
                $models[] = $class;
            }
        }

        return $models;
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

    private function getClassFromFile(string $file): ?string
    {
        $content = file_get_contents($file);
        if (! preg_match('/namespace\s+(\S+);/i', $content, $namespace)) {
            return null;
        }

        if (! preg_match('/class\s+(\w+)\s+(?:extends|implements|{)/i', $content, $class)) {
            return null;
        }

        return $namespace[1].'\\'.$class[1];
    }
}
