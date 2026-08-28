<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

enum ReportExportState: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Ready = 'ready';
    case Failed = 'failed';
}
