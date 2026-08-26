<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\ActivateDepartment;
use App\Domains\Organisation\Actions\CreateDepartment;
use App\Domains\Organisation\Actions\DeactivateDepartment;
use App\Domains\Organisation\Actions\UpdateDepartment;
use App\Domains\Organisation\Http\Requests\StoreDepartmentRequest;
use App\Domains\Organisation\Http\Requests\UpdateDepartmentRequest;
use App\Domains\Organisation\Http\Resources\DepartmentResource;
use App\Domains\Organisation\Models\Department;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Department::class);
        $departments = Department::paginate($request->input('per_page', 25));

        return response()->json([
            'data' => DepartmentResource::collection($departments),
            'meta' => [
                'page' => $departments->currentPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
            ],
            'links' => [
                'first' => $departments->url(1),
                'last' => $departments->url($departments->lastPage()),
                'prev' => $departments->previousPageUrl(),
                'next' => $departments->nextPageUrl(),
            ],
        ]);
    }

    public function show(Department $department)
    {
        $this->authorize('view', $department);

        return response()->json(['data' => new DepartmentResource($department)]);
    }

    public function store(StoreDepartmentRequest $request, CreateDepartment $action)
    {
        $this->authorize('create', Department::class);
        $department = $action->execute($request->validated(), $request->user());

        return response()->json(['data' => new DepartmentResource($department)], 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartment $action)
    {
        $this->authorize('update', $department);
        $department = $action->execute($department, $request->validated(), $request->user());

        return response()->json(['data' => new DepartmentResource($department)]);
    }

    public function activate(Department $department, ActivateDepartment $action)
    {
        $this->authorize('update', $department);
        $department = $action->execute($department, auth()->user());

        return response()->json(['data' => new DepartmentResource($department)]);
    }

    public function deactivate(Department $department, DeactivateDepartment $action, Request $request)
    {
        $this->authorize('delete', $department);
        $department = $action->execute($department, $request->all(), auth()->user());

        return response()->json(['data' => new DepartmentResource($department)]);
    }
}
