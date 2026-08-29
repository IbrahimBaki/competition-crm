<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\AuthenticateStaff;
use App\Domains\Security\Http\Requests\LoginRequest;
use App\Domains\Security\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthenticateStaff $action): UserResource
    {
        $user = $action->execute(
            $request->input('email'),
            $request->input('password'),
            $request->ip(),
        );

        if ($user->hasTwoFactorEnabled()) {
            if (! $request->hasSession()) {
                throw new \RuntimeException('Two-factor login requires a stateful frontend session.');
            }
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put('auth.two_factor_pending_user', $user->getKey());
            $request->session()->regenerate();

            return (new UserResource($user))->additional([
                'meta' => [
                    'two_factor_required' => true,
                ],
            ]);
        }

        Auth::guard('web')->login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
        $token = $user->createToken('web')->plainTextToken;

        return (new UserResource($user))->additional([
            'meta' => [
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }
        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(status: 204);
    }
}
