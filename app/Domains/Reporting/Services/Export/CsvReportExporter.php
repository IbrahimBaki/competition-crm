<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Export;

use App\Domains\Reporting\Models\ReportExportFormat;
use App\Domains\Reporting\Services\Definitions\ReportDefinition;
use App\Domains\Reporting\Services\Definitions\ReportResult;

class CsvReportExporter implements ReportExporter
{
    public function format(): ReportExportFormat
    {
        return ReportExportFormat::Csv;
    }

    public function render(ReportResult $result, ReportDefinition $definition): string
    {
        $stream = fopen('php://memory', 'r+');

        // Write UTF-8 BOM for Excel + Arabic support
        fwrite($stream, "\xEF\xBB\xBF");

        // Write header row
        fputcsv($stream, $definition->columns());

        // Write data rows
        foreach ($result->rows as $row) {
            $values = [];
            foreach ($definition->columns() as $column) {
                $values[] = $row[$column] ?? '';
            }
            fputcsv($stream, $values);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }
}
