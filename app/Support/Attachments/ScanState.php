<?php

namespace App\Support\Attachments;

enum ScanState: string
{
    case Pending = 'pending';
    case Clean = 'clean';
    case Infected = 'infected';
    case Failed = 'failed';
}
