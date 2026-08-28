<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Export;

use App\Domains\Reporting\Models\ReportExportFormat;

final class ReportExporterRegistry
{
    private array $exporters = [];

    public function register(ReportExporter $exporter): self
    {
        $this->exporters[$exporter->format()->value] = $exporter;

        return $this;
    }

    public function resolve(ReportExportFormat $format): ReportExporter
    {
        if (! isset($this->exporters[$format->value])) {
            throw new \InvalidArgumentException("No exporter for format: {$format->value}");
        }

        return $this->exporters[$format->value];
    }
}
