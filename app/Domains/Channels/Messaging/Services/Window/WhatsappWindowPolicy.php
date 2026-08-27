<?php

namespace App\Domains\Channels\Messaging\Services\Window;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonInterface;

final class WhatsappWindowPolicy
{
    public function isOpen(Ticket $ticket, CarbonInterface $at): bool
    {
        $expiresAt = $this->expiresAt($ticket);

        if ($expiresAt === null) {
            return false;
        }

        return $at < $expiresAt;
    }

    public function expiresAt(Ticket $ticket): ?CarbonInterface
    {
        $lastInboundMessage = $ticket->messages()
            ->where('channel', MessageChannel::Whatsapp)
            ->where('direction', MessageDirection::Inbound)
            ->orderByDesc('created_at')
            ->first();

        if ($lastInboundMessage === null) {
            return null;
        }

        return $lastInboundMessage->created_at->addHours(
            config('channels.whatsapp.free_form_window_hours', 24)
        );
    }
}
