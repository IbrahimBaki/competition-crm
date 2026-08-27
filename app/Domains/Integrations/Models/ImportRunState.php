<?php

namespace App\Domains\Integrations\Models;

enum ImportRunState: string
{
    case Pending = 'pending';
    case Validating = 'validating';
    case Importing = 'importing';
    case Completed = 'completed';
    case Failed = 'failed';
}
