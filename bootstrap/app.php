<?php

use App\Domains\Organisation\Exceptions\BranchHasActiveDepartmentsException;
use App\Domains\Organisation\Exceptions\DepartmentInUseException;
use App\Domains\Organisation\Exceptions\UserBranchNotAttachedException;
use App\Domains\Security\Http\Middleware\EnforceTwoFactorPolicy;
use App\Domains\Security\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Auth\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->appendToGroup('api', AuthenticateSession::class);
        $middleware->appendToGroup('api', EnsureAccountIsActive::class);
        $middleware->appendToGroup('api', EnforceTwoFactorPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                if ($e instanceof DepartmentInUseException) {
                    return response()->json([
                        'error' => [
                            'code' => 'department.has_open_tickets',
                            'message' => $e->getMessage(),
                            'meta' => [
                                'open_tickets' => $e->openTicketCount,
                            ],
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 409);
                }

                if ($e instanceof BranchHasActiveDepartmentsException) {
                    return response()->json([
                        'error' => [
                            'code' => 'branch.has_active_departments',
                            'message' => $e->getMessage(),
                            'meta' => [
                                'active_departments' => $e->activeDepartmentCount,
                            ],
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 409);
                }

                if ($e instanceof UserBranchNotAttachedException) {
                    return response()->json([
                        'error' => [
                            'code' => 'user.branch_not_attached',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof AdministratorRoleLockedException) {
                    return response()->json([
                        'error' => [
                            'code' => 'admin_role_locked',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof SystemRoleImmutableException) {
                    return response()->json([
                        'error' => [
                            'code' => 'system_role_immutable',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'error' => [
                            'code' => 'validation_failed',
                            'message' => 'The given data was invalid.',
                            'field_errors' => $e->errors(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof AuthorizationException) {
                    return response()->json([
                        'error' => [
                            'code' => 'unauthorized',
                            'message' => 'This action is unauthorized.',
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 403);
                }

                if ($e instanceof InvitationExpiredException) {
                    return response()->json([
                        'error' => [
                            'code' => 'invitation_expired',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof InvitationAlreadyAcceptedException) {
                    return response()->json([
                        'error' => [
                            'code' => 'invitation_invalid',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof InvitationAlreadyPendingException) {
                    return response()->json([
                        'error' => [
                            'code' => 'invitation_already_pending',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof AccountLockedException) {
                    return response()->json([
                        'error' => [
                            'code' => 'account_locked',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 423);
                }

                if ($e instanceof AccountDeactivatedException) {
                    return response()->json([
                        'error' => [
                            'code' => 'account_deactivated',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 401);
                }

                if ($e instanceof CannotDeactivateSelfException) {
                    return response()->json([
                        'error' => [
                            'code' => 'cannot_deactivate_self',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 409);
                }

                if ($e instanceof CannotDeactivateLastAdministratorException) {
                    return response()->json([
                        'error' => [
                            'code' => 'cannot_deactivate_last_administrator',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 409);
                }

                if ($e instanceof InvalidTwoFactorCodeException) {
                    return response()->json([
                        'error' => [
                            'code' => 'invalid_two_factor_code',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof TwoFactorRequiredException) {
                    return response()->json([
                        'error' => [
                            'code' => 'two_factor_required',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 202);
                }

                if ($e instanceof TwoFactorAlreadyEnabledException) {
                    return response()->json([
                        'error' => [
                            'code' => 'two_factor_already_enabled',
                            'message' => $e->getMessage(),
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 422);
                }

                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'error' => [
                            'code' => 'not_found',
                            'message' => 'Resource not found.',
                            'request_id' => request()->header('X-Request-Id') ?? Str::uuid(),
                        ],
                    ], 404);
                }
            }
        });
    })->create();
