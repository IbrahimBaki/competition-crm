<?php

namespace App\Domains\Integrations\Http\Middleware;

use App\Domains\Integrations\Exceptions\InsufficientTokenScopeException;
use App\Domains\Integrations\Models\ApiScope;
use App\Domains\Security\Permissions\PermissionKey;
use Closure;
use Illuminate\Http\Request;

final class RequireTokenScope
{
    public function handle(Request $request, Closure $next, string $scope)
    {
        $token = $request->attributes->get('api_token');

        if (! $token) {
            abort(401, 'Unauthenticated.');
        }

        // Validate scope format and get the enum
        try {
            $requiredScope = ApiScope::from($scope);
        } catch (\ValueError) {
            abort(500, 'Invalid scope configuration');
        }

        // Check if token has the required scope
        if (! $token->hasScope($requiredScope)) {
            throw new InsufficientTokenScopeException($scope);
        }

        // Also verify the scope's mapped permission key
        $permissionKey = $requiredScope->permission();
        if (! in_array($permissionKey->value, PermissionKey::all(), true)) {
            abort(500, 'Invalid scope configuration');
        }

        return $next($request);
    }
}
