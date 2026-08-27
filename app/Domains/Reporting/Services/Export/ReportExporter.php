<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Export;

use App\Domains\Reporting\Models\ReportExportFormat;
use App\Domains\Reporting\Services\Definitions\ReportDefinition;
use App\Domains\Reporting\Services\Definitions\ReportResult;

interface ReportExporter
{
    public function format(): ReportExportFormat;

    /**
     * Render the report result to export format bytes.
     */
    public function render(ReportResult $result, ReportDefinition $definition): string;
}
