<?php

namespace App\Domains\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->deactivated_at !== null) {
            return response()->json([
                'error' => [
                    'code' => 'account_deactivated',
                    'message' => 'Your account has been deactivated.',
                    'request_id' => $request->header('X-Request-Id') ?? Str::uuid(),
                ],
            ], 401);
        }

        return $next($request);
    }
}
