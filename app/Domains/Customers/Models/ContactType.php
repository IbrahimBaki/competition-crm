<?php

namespace App\Domains\Customers\Models;

enum ContactType: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case PortalLogin = 'portal_login';
}
