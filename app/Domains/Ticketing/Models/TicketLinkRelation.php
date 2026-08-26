<?php

namespace App\Domains\Ticketing\Models;

enum TicketLinkRelation: string
{
    case Related = 'related';
    case DuplicateOf = 'duplicate_of';
    case Blocks = 'blocks';

    public function inverse(): self
    {
        return match ($this) {
            self::Related => self::Related,
            self::DuplicateOf => self::DuplicateOf,
            self::Blocks => self::Blocks,
        };
    }
}
