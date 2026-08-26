<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\ActivateBranch;
use App\Domains\Organisation\Actions\CreateBranch;
use App\Domains\Organisation\Actions\DeactivateBranch;
use App\Domains\Organisation\Actions\UpdateBranch;
use App\Domains\Organisation\Http\Requests\StoreBranchRequest;
use App\Domains\Organisation\Http\Requests\UpdateBranchRequest;
use App\Domains\Organisation\Http\Resources\BranchResource;
use App\Domains\Organisation\Models\Branch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);

        $branches = Branch::paginate($request->input('per_page', 25));

        return response()->json([
            'data' => BranchResource::collection($branches),
            'meta' => [
                'page' => $branches->currentPage(),
                'per_page' => $branches->perPage(),
                'total' => $branches->total(),
            ],
            'links' => [
                'first' => $branches->url(1),
                'last' => $branches->url($branches->lastPage()),
                'prev' => $branches->previousPageUrl(),
                'next' => $branches->nextPageUrl(),
            ],
        ]);
    }

    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);

        return response()->json([
            'data' => new BranchResource($branch),
        ]);
    }

    public function store(StoreBranchRequest $request, CreateBranch $action)
    {
        $this->authorize('create', Branch::class);

        $branch = $action->execute($request->validated(), $request->user());

        return response()->json([
            'data' => new BranchResource($branch),
        ], 201);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action)
    {
        $this->authorize('update', $branch);

        $branch = $action->execute($branch, $request->validated(), $request->user());

        return response()->json([
            'data' => new BranchResource($branch),
        ]);
    }

    public function activate(Branch $branch, ActivateBranch $action)
    {
        $this->authorize('update', $branch);

        $branch = $action->execute($branch, auth()->user());

        return response()->json([
            'data' => new BranchResource($branch),
        ]);
    }

    public function deactivate(Branch $branch, DeactivateBranch $action)
    {
        $this->authorize('delete', $branch);

        $branch = $action->execute($branch, auth()->user());

        return response()->json([
            'data' => new BranchResource($branch),
        ]);
    }
}
