<?php

namespace App\Domains\Integrations\Http\Middleware;

use App\Domains\Integrations\Models\ApiToken;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

final class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $plaintext = substr($authHeader, 7);
        $hash = hash('sha256', $plaintext);

        $token = ApiToken::where('token_hash', $hash)
            ->where(function ($q) {
                $q->where('revoked_at', null)
                    ->where(function ($q2) {
                        $q2->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->first();

        if (! $token) {
            throw new AuthenticationException('Unauthenticated.');
        }

        // Touch last_used_at throttled to once per 60 seconds
        $token->touchLastUsedAt();

        // Bind token to request for downstream middleware
        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
