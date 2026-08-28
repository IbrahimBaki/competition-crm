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
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'name', 'created_at', '-id', '-name', '-created_at'])
            ->withFilters(['is_system' => ['eq', 'neq']])
            ->withSearchableColumns(['name']);

        $query = new CollectionQuery($request, $spec);
        $data = $query->paginate(Role::query());

        return ApiResponse::collection(
            $data,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return ApiResponse::item(new RoleResource($role->load('permissions')));
    }

    public function store(StoreRoleRequest $request, CreateRole $action)
    {
        $this->authorize('create', Role::class);

        $role = $action->execute($request->user(), $request->validated());

        return ApiResponse::item(new RoleResource($role), 201);
    }

    public function update(UpdateRoleRequest $request, Role $role, UpdateRole $action)
    {
        $this->authorize('update', $role);

        $updated = $action->execute($request->user(), $role, $request->validated());

        return ApiResponse::item(new RoleResource($updated));
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->is_system) {
            throw new SystemRoleImmutableException;
        }

        $role->delete();

        return ApiResponse::noContent();
    }

    public function attachUser(Request $request, Role $role, User $user, AuditLogger $auditLogger)
    {
        $this->authorize('update', $role);

        $action = new AssignRoleToUser($auditLogger);
        $action->execute($request->user(), $user, $role->id);

        return ApiResponse::noContent();
    }

    public function detachUser(Request $request, Role $role, User $user, AuditLogger $auditLogger)
    {
        $this->authorize('update', $role);

        $action = new DetachRoleFromUser($auditLogger);
        $action->execute($request->user(), $user, $role->id);

        return ApiResponse::noContent();
    }
}
