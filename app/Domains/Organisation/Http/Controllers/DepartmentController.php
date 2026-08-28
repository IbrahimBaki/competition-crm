<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\ActivateDepartment;
use App\Domains\Organisation\Actions\CreateDepartment;
use App\Domains\Organisation\Actions\DeactivateDepartment;
use App\Domains\Organisation\Actions\DeleteDepartment;
use App\Domains\Organisation\Actions\UpdateDepartment;
use App\Domains\Organisation\Http\Requests\StoreDepartmentRequest;
use App\Domains\Organisation\Http\Requests\UpdateDepartmentRequest;
use App\Domains\Organisation\Http\Resources\DepartmentResource;
use App\Domains\Organisation\Models\Department;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Department::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'name', 'created_at', '-id', '-name', '-created_at'])
            ->withFilters(['is_active' => ['eq', 'neq']])
            ->withSearchableColumns(['name']);

        $query = new CollectionQuery($request, $spec);
        $departments = $query->paginate(Department::query());

        return ApiResponse::collection(
            $departments,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function show(Department $department)
    {
        $this->authorize('view', $department);

        return ApiResponse::item(new DepartmentResource($department));
    }

    public function store(StoreDepartmentRequest $request, CreateDepartment $action)
    {
        $this->authorize('create', Department::class);
        $department = $action->execute($request->validated(), $request->user());

        return ApiResponse::item(new DepartmentResource($department), 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartment $action)
    {
        $this->authorize('update', $department);
        $department = $action->execute($department, $request->validated(), $request->user());

        return ApiResponse::item(new DepartmentResource($department));
    }

    public function activate(Department $department, ActivateDepartment $action)
    {
        $this->authorize('update', $department);
        $department = $action->execute($department, auth()->user());

        return ApiResponse::item(new DepartmentResource($department));
    }

    public function deactivate(Department $department, DeactivateDepartment $action, Request $request)
    {
        $this->authorize('delete', $department);
        $department = $action->execute($department, $request->all(), auth()->user());

        return ApiResponse::item(new DepartmentResource($department));
    }

    public function destroy(Department $department, DeleteDepartment $action)
    {
        $this->authorize('delete', $department);

        $action->execute($department, auth()->user());

        return ApiResponse::noContent();
    }
}
