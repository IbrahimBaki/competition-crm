<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\ActivateUser;
use App\Domains\Security\Actions\DeactivateUser;
use App\Domains\Security\Actions\InviteUser;
use App\Domains\Security\Http\Requests\ActivateUserRequest;
use App\Domains\Security\Http\Requests\DeactivateUserRequest;
use App\Domains\Security\Http\Requests\InviteUserRequest;
use App\Domains\Security\Http\Resources\InvitationResource;
use App\Domains\Security\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class UserLifecycleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::paginate($request->query('per_page', 25));

        return UserResource::collection($users);
    }

    public function invite(InviteUserRequest $request, InviteUser $action): InvitationResource
    {
        [$invitation, $rawToken] = $action->execute(
            $request->user(),
            $request->input('email'),
        );

        return new InvitationResource($invitation);
    }

    public function deactivate(DeactivateUserRequest $request, User $user, DeactivateUser $action)
    {
        $this->authorize('deactivate', $user);

        $action->execute($request->user(), $user);

        return response()->json(status: 204);
    }

    public function activate(ActivateUserRequest $request, User $user, ActivateUser $action)
    {
        $this->authorize('activate', $user);

        $action->execute($request->user(), $user);

        return response()->json(status: 204);
    }
}
