<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Definitions;

use App\Domains\Reporting\Exceptions\UnknownReportException;

final class ReportRegistry
{
    private array $definitions = [];

    public function register(ReportDefinition $definition): self
    {
        $this->definitions[$definition->key()] = $definition;

        return $this;
    }

    public function resolve(string $key): ReportDefinition
    {
        if (! isset($this->definitions[$key])) {
            throw UnknownReportException::for($key);
        }

        return $this->definitions[$key];
    }

    /**
     * Get all registered report definitions.
     *
     * @return array<string, ReportDefinition>
     */
    public function all(): array
    {
        return $this->definitions;
    }
}
