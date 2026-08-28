<?php

namespace App\Domains\Customers\Models;

enum ContactType: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case PortalLogin = 'portal_login';
    case Sms = 'sms';
    case WebForm = 'web_form';
    case Chat = 'chat';

    /** Types compared as phone numbers (E.164, country code included). */
    public function isPhoneLike(): bool
    {
        return in_array($this, [self::Phone, self::Whatsapp, self::Sms], true);
    }

    /** Types that participate in cross-channel identity resolution. */
    public function isResolvable(): bool
    {
        return $this !== self::WebForm;
    }
}
