<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\AssignRoleToUser;
use App\Domains\Security\Actions\CreateRole;
use App\Domains\Security\Actions\DetachRoleFromUser;
use App\Domains\Security\Actions\UpdateRole;
use App\Domains\Security\Exceptions\SystemRoleImmutableException;
use App\Domains\Security\Http\Requests\StoreRoleRequest;
use App\Domains\Security\Http\Requests\UpdateRoleRequest;
use App\Domains\Security\Http\Resources\RoleResource;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')
            ->paginate($request->query('per_page', 25));

        return RoleResource::collection($roles);
    }

    public function show(Role $role): RoleResource
    {
        $this->authorize('view', $role);

        return new RoleResource($role->load('permissions'));
    }

    public function store(StoreRoleRequest $request): RoleResource
    {
        $this->authorize('create', Role::class);

        $action = new CreateRole;
        $role = $action->execute($request->validated());

        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->authorize('update', $role);

        $action = new UpdateRole;
        $updated = $action->execute($role, $request->validated());

        return new RoleResource($updated);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        if ($role->is_system) {
            throw new SystemRoleImmutableException;
        }

        $role->delete();

        return response()->json(status: 204);
    }

    public function attachUser(Request $request, Role $role, User $user, AuditLogger $auditLogger): JsonResponse
    {
        $this->authorize('update', $role);

        $action = new AssignRoleToUser($auditLogger);
        $action->execute($request->user(), $user, $role->id);

        return response()->json(status: 204);
    }

    public function detachUser(Request $request, Role $role, User $user, AuditLogger $auditLogger): JsonResponse
    {
        $this->authorize('update', $role);

        $action = new DetachRoleFromUser($auditLogger);
        $action->execute($request->user(), $user, $role->id);

        return response()->json(status: 204);
    }
}
