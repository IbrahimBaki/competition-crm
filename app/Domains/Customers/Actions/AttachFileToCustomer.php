<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Security\Services\AuditLogger;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\Exceptions\AttachmentInfectedException;
use App\Support\Attachments\Exceptions\AttachmentPendingScanException;
use App\Support\Attachments\ScanState;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class AttachFileToCustomer
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(
        Customer $customer,
        Attachment $attachment,
        ?Authenticatable $actor = null,
    ): Attachment {
        if ($attachment->scan_state === ScanState::Pending) {
            throw new AttachmentPendingScanException('Attachment is still being scanned');
        }

        if ($attachment->scan_state === ScanState::Infected) {
            throw new AttachmentInfectedException('Attachment failed the malware scan');
        }

        return \DB::transaction(function () use ($customer, $attachment, $actor) {
            $attachment->attachable_type = Customer::class;
            $attachment->attachable_id = $customer->id;
            $attachment->save();

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::AttachmentAdded->value,
                'actor_user_id' => $actor?->id,
                'payload' => ['attachment_uuid' => $attachment->uuid],
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.attachment.added',
                $customer,
                null,
                $attachment->toArray()
            );

            return $attachment;
        });
    }
}
