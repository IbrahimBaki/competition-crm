<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\ActivateBranch;
use App\Domains\Organisation\Actions\CreateBranch;
use App\Domains\Organisation\Actions\DeactivateBranch;
use App\Domains\Organisation\Actions\DeleteBranch;
use App\Domains\Organisation\Actions\UpdateBranch;
use App\Domains\Organisation\Http\Requests\StoreBranchRequest;
use App\Domains\Organisation\Http\Requests\UpdateBranchRequest;
use App\Domains\Organisation\Http\Resources\BranchResource;
use App\Domains\Organisation\Models\Branch;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'name', 'created_at', '-id', '-name', '-created_at'])
            ->withFilters(['is_active' => ['eq', 'neq']])
            ->withSearchableColumns(['name']);

        $query = new CollectionQuery($request, $spec);
        $branches = $query->paginate(Branch::query());

        return ApiResponse::collection(
            $branches,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);

        return ApiResponse::item(new BranchResource($branch));
    }

    public function store(StoreBranchRequest $request, CreateBranch $action)
    {
        $this->authorize('create', Branch::class);

        $branch = $action->execute($request->validated(), $request->user());

        return ApiResponse::item(new BranchResource($branch), 201);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action)
    {
        $this->authorize('update', $branch);

        $branch = $action->execute($branch, $request->validated(), $request->user());

        return ApiResponse::item(new BranchResource($branch));
    }

    public function activate(Branch $branch, ActivateBranch $action)
    {
        $this->authorize('update', $branch);

        $branch = $action->execute($branch, auth()->user());

        return ApiResponse::item(new BranchResource($branch));
    }

    public function deactivate(Branch $branch, DeactivateBranch $action)
    {
        $this->authorize('delete', $branch);

        $branch = $action->execute($branch, auth()->user());

        return ApiResponse::item(new BranchResource($branch));
    }

    public function destroy(Branch $branch, DeleteBranch $action)
    {
        $this->authorize('delete', $branch);

        $action->execute($branch, auth()->user());

        return ApiResponse::noContent();
    }
}
