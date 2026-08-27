<?php

namespace App\Domains\Channels\Chat\Models;

enum ChatSessionState: string
{
    case Requested = 'requested';
    case Queued = 'queued';
    case Active = 'active';
    case Transferred = 'transferred';
    case Ended = 'ended';
    case Abandoned = 'abandoned';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Ended, self::Abandoned], true);
    }

    public function isLive(): bool
    {
        return in_array($this, [self::Requested, self::Queued, self::Active, self::Transferred], true);
    }
}
