<?php

namespace App\Domains\Channels\Messaging\Services\Consent;

use App\Domains\Channels\Messaging\Exceptions\SmsOptedOutException;
use App\Domains\Channels\Messaging\Exceptions\WhatsappOptInRequiredException;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Ticketing\Models\MessageChannel;

final class MessagingConsentGuard
{
    public function assertMayReceive(CustomerContact $contact, MessageChannel $channel): void
    {
        match ($channel) {
            MessageChannel::Whatsapp => $this->assertWhatsappOptIn($contact),
            MessageChannel::Sms => $this->assertSmsNotOptedOut($contact),
            default => null,
        };
    }

    private function assertWhatsappOptIn(CustomerContact $contact): void
    {
        if ($contact->whatsapp_opted_in_at === null) {
            throw new WhatsappOptInRequiredException;
        }
    }

    private function assertSmsNotOptedOut(CustomerContact $contact): void
    {
        if ($contact->sms_opted_out_at !== null) {
            throw new SmsOptedOutException;
        }
    }
}
