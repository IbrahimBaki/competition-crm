<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\ActivateUser;
use App\Domains\Security\Actions\DeactivateUser;
use App\Domains\Security\Actions\InviteUser;
use App\Domains\Security\Http\Requests\ActivateUserRequest;
use App\Domains\Security\Http\Requests\DeactivateUserRequest;
use App\Domains\Security\Http\Requests\InviteUserRequest;
use App\Domains\Security\Http\Resources\InvitationResource;
use App\Models\User;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserLifecycleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'email', 'created_at', '-id', '-email', '-created_at'])
            ->withFilters(['is_active' => ['eq', 'neq']])
            ->withSearchableColumns(['email', 'name']);

        $query = new CollectionQuery($request, $spec);
        $data = $query->paginate(User::query());

        return ApiResponse::collection(
            $data,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
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

        return ApiResponse::noContent();
    }

    public function activate(ActivateUserRequest $request, User $user, ActivateUser $action)
    {
        $this->authorize('activate', $user);

        $action->execute($request->user(), $user);

        return ApiResponse::noContent();
    }
}
