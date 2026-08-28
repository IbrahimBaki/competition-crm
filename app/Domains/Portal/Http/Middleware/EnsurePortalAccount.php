<?php

namespace App\Domains\Portal\Http\Middleware;

use App\Domains\Portal\Exceptions\PortalSessionInvalidException;
use App\Domains\Portal\Models\PortalAccount;
use Closure;
use Illuminate\Http\Request;

class EnsurePortalAccount
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth('portal')->check()) {
            throw new PortalSessionInvalidException;
        }

        $user = auth('portal')->user();

        if (! $user instanceof PortalAccount) {
            throw new PortalSessionInvalidException;
        }

        if ($user->deactivated_at !== null) {
            throw new PortalSessionInvalidException;
        }

        return $next($request);
    }
}
