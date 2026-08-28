<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\CompletePasswordReset;
use App\Domains\Security\Actions\RequestPasswordReset;
use App\Domains\Security\Http\Requests\RequestPasswordResetRequest;
use App\Domains\Security\Http\Requests\ResetPasswordRequest;
use Illuminate\Routing\Controller;

class PasswordResetController extends Controller
{
    public function forgot(RequestPasswordResetRequest $request, RequestPasswordReset $action)
    {
        $action->execute($request->input('email'));

        return response()->json(status: 202);
    }

    public function reset(ResetPasswordRequest $request, CompletePasswordReset $action)
    {
        $action->execute(
            $request->input('email'),
            $request->input('token'),
            $request->input('password'),
        );

        return response()->json(status: 200);
    }
}
