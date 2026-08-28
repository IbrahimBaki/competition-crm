<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

enum ReportExportFormat: string
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
    case Pdf = 'pdf';
}
