<?php

namespace App\Domains\Portal\Services\Visibility;

use App\Domains\Portal\Models\PortalAccount;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\ScanState;

class PortalAttachmentGuard
{
    public function __construct(private PortalTicketScope $ticketScope) {}

    public function canDownload(PortalAccount $account, Attachment $attachment): bool
    {
        if ($attachment->scan_state !== ScanState::Clean) {
            return false;
        }

        $message = $attachment->attachable;

        if (! $message || $message->is_internal) {
            return false;
        }

        $ticket = $message->ticket;

        if (! $ticket) {
            return false;
        }

        try {
            $this->ticketScope->findOrFail($account, $ticket->uuid);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
