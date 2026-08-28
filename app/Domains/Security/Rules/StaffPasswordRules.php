<?php

namespace App\Domains\Security\Rules;

use Illuminate\Validation\Rules\Password;

final class StaffPasswordRules
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
