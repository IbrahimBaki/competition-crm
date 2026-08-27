<?php

namespace App\Domains\Channels\Email\Services\Correlation;

use App\Domains\Channels\Email\Services\Parsing\ParsedEmail;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;

class EmailCorrelator
{
    public function correlate(ParsedEmail $email): ?Ticket
    {
        // 1. Match In-Reply-To against ticket_messages.external_message_id
        if ($email->inReplyTo) {
            $ticket = $this->findTicketByExternalMessageId($email->inReplyTo);
            if ($ticket) {
                return $this->followMerge($ticket);
            }
        }

        // 2. Match References (most recent last) - iterate in reverse
        $references = array_reverse($email->referenceIds);
        foreach ($references as $reference) {
            $ticket = $this->findTicketByExternalMessageId($reference);
            if ($ticket) {
                return $this->followMerge($ticket);
            }
        }

        // 3. Reference-token match in subject then body
        $reference = $this->extractTicketReference($email->subject ?? '')
            ?? $this->extractTicketReference($email->textBody);

        if ($reference) {
            $ticket = Ticket::where('reference', $reference)->first();
            if ($ticket) {
                return $this->followMerge($ticket);
            }
        }

        // 4. No correlation found
        return null;
    }

    private function findTicketByExternalMessageId(string $messageId): ?Ticket
    {
        $message = TicketMessage::where('channel', MessageChannel::Email->value)
            ->where('external_message_id', $messageId)
            ->first();

        return $message?->ticket;
    }

    private function extractTicketReference(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        if (preg_match('/\bTKT-\d{6}-\d{6}\b/', $text, $m)) {
            return $m[0];
        }

        return null;
    }

    private function followMerge(Ticket $ticket): Ticket
    {
        if ($ticket->isMerged() && $ticket->merged_into_id) {
            $target = Ticket::find($ticket->merged_into_id);

            return $target ?? $ticket;
        }

        return $ticket;
    }
}
