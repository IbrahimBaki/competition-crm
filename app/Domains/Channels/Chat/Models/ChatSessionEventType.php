<?php

namespace App\Domains\Channels\Chat\Models;

enum ChatSessionEventType: string
{
    case Requested = 'requested';
    case Queued = 'queued';
    case Accepted = 'accepted';
    case Reconnected = 'reconnected';
    case Transferred = 'transferred';
    case Ended = 'ended';
    case Abandoned = 'abandoned';
    case TranscriptPersisted = 'transcript_persisted';
}
