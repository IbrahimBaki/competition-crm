<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

enum ReportFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
}
