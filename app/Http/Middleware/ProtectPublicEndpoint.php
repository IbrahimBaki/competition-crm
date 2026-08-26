<?php

namespace App\Http\Middleware;

use App\Support\Http\BotProtection\BotProtectionGuard;
use App\Support\Http\Exceptions\BotProtectionFailedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectPublicEndpoint
{
    public function __construct(
        private readonly BotProtectionGuard $guard,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->guard->verify($request)) {
            throw new BotProtectionFailedException;
        }

        return $next($request);
    }
}
