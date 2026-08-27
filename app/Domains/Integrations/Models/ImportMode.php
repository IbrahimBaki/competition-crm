<?php

namespace App\Domains\Integrations\Models;

enum ImportMode: string
{
    case DryRun = 'dry_run';
    case Commit = 'commit';
}
