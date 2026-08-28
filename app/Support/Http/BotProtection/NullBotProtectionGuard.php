<?php

namespace App\Support\Http\BotProtection;

use Illuminate\Http\Request;

final class NullBotProtectionGuard implements BotProtectionGuard
{
    public function verify(Request $request): bool
    {
        return true;
    }
}
