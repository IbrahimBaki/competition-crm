<?php

use App\Domains\Integrations\Http\Middleware\AuthenticateApiToken;
use App\Domains\Integrations\Http\Middleware\RequireTokenScope;
use App\Domains\Portal\Http\Middleware\DenyPortalToken;
use App\Domains\Portal\Http\Middleware\EnsurePortalAccount;
use App\Domains\Security\Http\Middleware\EnforceTwoFactorPolicy;
use App\Domains\Security\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnforceIdempotency;
use App\Http\Middleware\NegotiateLocale;
use App\Http\Middleware\ProtectPublicEndpoint;
use App\Support\Http\Errors\ApiExceptionRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            AssignRequestId::class,
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'bot.protect' => ProtectPublicEndpoint::class,
            'public.protect' => ProtectPublicEndpoint::class,
            'portal.auth' => EnsurePortalAccount::class,
            'portal.deny' => DenyPortalToken::class,
            'api.auth' => AuthenticateApiToken::class,
            'api.scope' => RequireTokenScope::class,
        ]);

        $middleware->appendToGroup('api', AuthenticateSession::class);
        $middleware->appendToGroup('api', NegotiateLocale::class);
        $middleware->appendToGroup('api', EnsureAccountIsActive::class);
        $middleware->appendToGroup('api', EnforceTwoFactorPolicy::class);
        $middleware->appendToGroup('api', EnforceIdempotency::class);
        $middleware->appendToGroup('api', 'throttle:api');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return app(ApiExceptionRenderer::class)->render($e, $request);
            }

            return null;
        });
    })->create();
