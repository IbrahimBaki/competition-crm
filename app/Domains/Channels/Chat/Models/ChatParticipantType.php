<?php

namespace App\Domains\Channels\Chat\Models;

use App\Domains\Ticketing\Models\MessageAuthorType;

enum ChatParticipantType: string
{
    case Visitor = 'visitor';
    case Agent = 'agent';
    case Bot = 'bot';
    case System = 'system';

    public function toMessageAuthorType(): MessageAuthorType
    {
        return match ($this) {
            self::Visitor => MessageAuthorType::Customer,
            self::Agent => MessageAuthorType::Agent,
            self::Bot => MessageAuthorType::Ai,
            self::System => MessageAuthorType::System,
        };
    }
}
