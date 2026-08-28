<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Domains\Notifications\Enums\NotificationEventType;

class WebFormSubmissionAcknowledgedEvent implements NotifiableEvent
{
    public function __construct(
        private readonly WebFormSubmission $submission,
    ) {}

    public function eventType(): NotificationEventType
    {
        return NotificationEventType::WebFormSubmissionAcknowledged;
    }

    public function eventId(): string
    {
        return $this->submission->uuid;
    }

    public function recipientIds(): array
    {
        return [];
    }

    public function toPayload(): array
    {
        return [
            'ticket_reference' => $this->submission->ticket->reference,
            'tracking_token' => $this->submission->tracking_token,
            'form_title' => (string) $this->submission->form->title,
            'customer_email' => $this->submission->customer?->contacts()
                ->where('type', 'email')
                ->first()?->value,
        ];
    }
}
