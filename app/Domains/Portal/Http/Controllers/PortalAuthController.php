<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Customers\Exceptions\CustomerBlockedException;
use App\Domains\Portal\Actions\AuthenticatePortalAccount;
use App\Domains\Portal\Actions\RegisterPortalAccount;
use App\Domains\Portal\Actions\VerifyPortalContact;
use App\Domains\Portal\Exceptions\PortalAccountNotVerifiedException;
use App\Domains\Portal\Http\Resources\PortalAccountResource;
use App\Support\Http\Errors\ErrorCode;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalAuthController
{
    public function register(Request $request, RegisterPortalAccount $action): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:portal_accounts'],
            'password' => ['required', 'string', 'min:12'],
            'password_confirmation' => ['required', 'same:password'],
            'locale' => ['nullable', 'in:en,ar'],
        ]);

        try {
            $account = $action->handle(
                $validated['email'],
                $validated['password'],
                $validated['locale'] ?? null
            );

            return response()->json([
                'data' => [
                    'uuid' => $account->uuid,
                    'email' => $account->email,
                    'message' => 'Verification email sent',
                ],
            ], 201);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'unique constraint')) {
                return response()->json([
                    'error' => [
                        'code' => ErrorCode::ValidationFailed->value,
                        'message' => 'Email already registered. Please verify your account or log in.',
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function verify(Request $request, VerifyPortalContact $action): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $account = $action->handle($validated['token']);

            return response()->json([
                'data' => new PortalAccountResource($account),
            ]);
        } catch (CustomerBlockedException $e) {
            return response()->json([
                'error' => [
                    'code' => ErrorCode::CustomerBlocked->value,
                    'message' => $e->getMessage(),
                ],
            ], 403);
        }
    }

    public function login(Request $request, AuthenticatePortalAccount $action): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $token = $action->execute(
                $validated['email'],
                $validated['password'],
                $request->ip() ?? '0.0.0.0'
            );

            return response()->json([
                'data' => ['token' => $token],
            ]);
        } catch (PortalAccountNotVerifiedException) {
            return response()->json([
                'error' => [
                    'code' => ErrorCode::PortalAccountNotVerified->value,
                    'message' => __('errors.portal.account_not_verified'),
                ],
            ], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('portal')->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
