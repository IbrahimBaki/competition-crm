<?php

namespace App\Domains\Customers\Models;

enum CustomerEventType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case StatusChanged = 'status_changed';
    case Blocked = 'blocked';
    case Unblocked = 'unblocked';
    case NoteAdded = 'note_added';
    case ContactAdded = 'contact_added';
    case ContactRemoved = 'contact_removed';
    case AttachmentAdded = 'attachment_added';
}
