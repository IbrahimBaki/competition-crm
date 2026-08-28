<?php

namespace App\Domains\Ai\Models;

enum AiSuggestionState: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Discarded = 'discarded';
    case Sent = 'sent';
}
