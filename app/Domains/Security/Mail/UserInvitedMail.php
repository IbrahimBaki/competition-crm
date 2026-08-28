<?php

namespace App\Domains\Security\Mail;

use App\Domains\Security\Models\UserInvitation;
use App\Support\I18n\LocalizationSettings;
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
        $locale = app(LocalizationSettings::class)->defaultLocale();

        return new Envelope(
            subject: __('errors.mail.invitation.subject', locale: $locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.security.user-invited',
        );
    }
}
