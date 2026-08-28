<?php

namespace App\Domains\Portal\Rules;

use Illuminate\Validation\Rules\Password;

final class PortalPasswordRules
{
    public static function default(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            Password::min(12)->mixedCase()->numbers()->symbols(),
        ];
    }
}
