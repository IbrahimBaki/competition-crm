<?php

namespace App\Domains\Ticketing\Models;

enum MessageChannel: string
{
    case Email = 'email';
    case Whatsapp = 'whatsapp';
    case Sms = 'sms';
    case Chat = 'chat';
    case Portal = 'portal';
    case Internal = 'internal';

    public function supportsReadReceipts(): bool
    {
        return in_array($this, [self::Whatsapp, self::Chat], true);
    }
}
