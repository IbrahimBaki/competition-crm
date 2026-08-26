<?php

namespace App\Support\Http\BotProtection;

use Illuminate\Http\Request;

interface BotProtectionGuard
{
    public function verify(Request $request): bool;
}
