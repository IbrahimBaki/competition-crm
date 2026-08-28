<?php

namespace App\Domains\Portal\Http\Middleware;

use App\Domains\Portal\Exceptions\PortalSessionInvalidException;
use App\Domains\Portal\Models\PortalAccount;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class DenyPortalToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            $personal = PersonalAccessToken::findToken($token);

            if ($personal && $personal->tokenable instanceof PortalAccount) {
                throw new PortalSessionInvalidException;
            }
        }

        return $next($request);
    }
}
