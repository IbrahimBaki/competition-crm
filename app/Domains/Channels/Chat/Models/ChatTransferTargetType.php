<?php

namespace App\Domains\Channels\Chat\Models;

enum ChatTransferTargetType: string
{
    case Agent = 'agent';
    case Bot = 'bot';
    case Queue = 'queue';
}
