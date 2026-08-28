<?php

namespace App\Domains\Workspace\Models;

enum QuickReplyScope: string
{
    case Personal = 'personal';
    case Shared = 'shared';
}
