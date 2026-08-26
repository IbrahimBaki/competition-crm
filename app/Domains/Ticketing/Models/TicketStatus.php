<?php

namespace App\Domains\Ticketing\Models;

enum TicketStatus: string
{
    case New = 'new';
    case Open = 'open';
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Spam = 'spam';

    public function stopsSlaClock(): bool
    {
        return in_array($this, [self::Pending, self::Resolved, self::Closed, self::Spam], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Closed, self::Spam], true);
    }
}
