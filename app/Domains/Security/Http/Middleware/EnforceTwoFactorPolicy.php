<?php

namespace App\Domains\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnforceTwoFactorPolicy
{
    private array $exemptPaths = [
        'v1/auth/logout',
        'v1/auth/two-factor',
        'v1/auth/two-factor/confirm',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $path = $request->getPathInfo();

        foreach ($this->exemptPaths as $exemptPath) {
            if (str_contains($path, $exemptPath)) {
                return $next($request);
            }
        }

        $requireTwoFactor = DB::table('auth_settings')
            ->where('id', 1)
            ->value('require_two_factor');

        if ($requireTwoFactor && ! Auth::user()->hasTwoFactorEnabled()) {
            return response()->json([
                'error' => [
                    'code' => 'two_factor_enrollment_required',
                    'message' => 'Two-factor authentication is required. Please enroll to continue.',
                    'request_id' => $request->header('X-Request-Id') ?? Str::uuid(),
                ],
            ], 403);
        }

        return $next($request);
    }
}
