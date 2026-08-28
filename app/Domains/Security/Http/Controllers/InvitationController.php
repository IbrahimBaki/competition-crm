<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\AcceptInvitation;
use App\Domains\Security\Http\Requests\AcceptInvitationRequest;
use App\Domains\Security\Http\Resources\UserResource;
use Illuminate\Routing\Controller;

class InvitationController extends Controller
{
    public function accept(AcceptInvitationRequest $request, AcceptInvitation $action): UserResource
    {
        $user = $action->execute(
            $request->input('token'),
            $request->input('name'),
            $request->input('password'),
        );

        return new UserResource($user);
    }
}
