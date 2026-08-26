<?php

namespace App\Domains\Automation\Models;

enum RuleExecutionOutcome: string
{
    case Matched = 'matched';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
