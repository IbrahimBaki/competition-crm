<?php

namespace App\Domains\Security\Mail;

use App\Domains\Security\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserInvitation $invitation,
        public string $rawToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to join Support CRM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.security.user-invited',
        );
    }
}
