<?php

namespace App\Domains\Channels\Messaging\Actions;

use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Security\Services\AuditLogger;

final class RecordMessagingConsent
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function optInWhatsapp(CustomerContact $contact, string $source = 'inbound_message'): void
    {
        $before = [
            'whatsapp_opted_in_at' => $contact->whatsapp_opted_in_at,
            'whatsapp_opt_in_source' => $contact->whatsapp_opt_in_source,
        ];

        $contact->update([
            'whatsapp_opted_in_at' => now(),
            'whatsapp_opt_in_source' => $source,
        ]);

        $this->auditLogger->record(
            actor: $contact,
            action: 'messaging.consent.opt_in',
            target: $contact,
            before: $before,
            after: [
                'whatsapp_opted_in_at' => $contact->whatsapp_opted_in_at,
                'whatsapp_opt_in_source' => $contact->whatsapp_opt_in_source,
            ],
        );
    }

    public function optOutSms(CustomerContact $contact, string $source = 'customer_request'): void
    {
        $before = [
            'sms_opted_out_at' => $contact->sms_opted_out_at,
            'sms_opt_out_source' => $contact->sms_opt_out_source,
        ];

        $contact->update([
            'sms_opted_out_at' => now(),
            'sms_opt_out_source' => $source,
        ]);

        $this->auditLogger->record(
            actor: $contact,
            action: 'messaging.consent.opt_out',
            target: $contact,
            before: $before,
            after: [
                'sms_opted_out_at' => $contact->sms_opted_out_at,
                'sms_opt_out_source' => $contact->sms_opt_out_source,
            ],
        );
    }

    public function optInSms(CustomerContact $contact, string $source = 'customer_request'): void
    {
        $before = [
            'sms_opted_out_at' => $contact->sms_opted_out_at,
            'sms_opt_out_source' => $contact->sms_opt_out_source,
        ];

        $contact->update([
            'sms_opted_out_at' => null,
            'sms_opt_out_source' => null,
        ]);

        $this->auditLogger->record(
            actor: $contact,
            action: 'messaging.consent.opt_in',
            target: $contact,
            before: $before,
            after: [
                'sms_opted_out_at' => null,
                'sms_opt_out_source' => null,
            ],
        );
    }
}
