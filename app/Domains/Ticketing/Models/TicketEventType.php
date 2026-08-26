<?php

namespace App\Domains\Ticketing\Models;

enum TicketEventType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Reclassified = 'reclassified';
    case Assigned = 'assigned';
    case Unassigned = 'unassigned';
    case PriorityChanged = 'priority_changed';
    case StatusChanged = 'status_changed';
    case TagAdded = 'tag_added';
    case TagRemoved = 'tag_removed';
    case CustomFieldsUpdated = 'custom_fields_updated';
    case Reopened = 'reopened';
    case MarkedSpam = 'marked_spam';
    case RestoredFromSpam = 'restored_from_spam';
    case Merged = 'merged';
    case Split = 'split';
    case Linked = 'linked';
    case Unlinked = 'unlinked';
    case MessagePosted = 'message_posted';
    case InternalNoteAdded = 'internal_note_added';
    case MessageRetried = 'message_retried';
    case Claimed = 'claimed';
    case TransferredToAgent = 'transferred_to_agent';
    case TransferredToDepartment = 'transferred_to_department';
    case SlaWarningRaised = 'sla_warning_raised';
    case SlaBreached = 'sla_breached';
    case SlaPaused = 'sla_paused';
    case SlaResumed = 'sla_resumed';
    case SlaRetargeted = 'sla_retargeted';
    case SlaReset = 'sla_reset';
}
