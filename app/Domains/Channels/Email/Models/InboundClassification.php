<?php

namespace App\Domains\Channels\Email\Models;

enum InboundClassification: string
{
    case Reply = 'reply';
    case New = 'new';
    case AutoReply = 'auto_reply';
    case Bounce = 'bounce';
    case Loop = 'loop';
}
