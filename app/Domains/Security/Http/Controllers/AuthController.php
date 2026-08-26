<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\AuthenticateStaff;
use App\Domains\Security\Http\Requests\LoginRequest;
use App\Domains\Security\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthenticateStaff $action): UserResource
    {
        $user = $action->execute(
            $request->input('email'),
            $request->input('password'),
            $request->ip(),
        );

        $token = $user->createToken('web')->plainTextToken;

        $request->session()->regenerate();

        return (new UserResource($user))->additional([
            'meta' => [
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(status: 204);
    }
}
