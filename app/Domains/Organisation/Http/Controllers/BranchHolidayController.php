<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\CreateBranchHoliday;
use App\Domains\Organisation\Actions\DeleteBranchHoliday;
use App\Domains\Organisation\Actions\UpdateBranchHoliday;
use App\Domains\Organisation\Http\Requests\StoreBranchHolidayRequest;
use App\Domains\Organisation\Http\Requests\UpdateBranchHolidayRequest;
use App\Domains\Organisation\Http\Resources\BranchHolidayResource;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchHoliday;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BranchHolidayController extends Controller
{
    use AuthorizesRequests;

    public function index(Branch $branch): AnonymousResourceCollection
    {
        $this->authorize('view', $branch);

        return BranchHolidayResource::collection($branch->holidays);
    }

    public function store(StoreBranchHolidayRequest $request, Branch $branch, CreateBranchHoliday $action): BranchHolidayResource
    {
        $this->authorize('update', $branch);

        $holiday = $action->execute($branch, $request->validated(), $request->user());

        return new BranchHolidayResource($holiday);
    }

    public function update(UpdateBranchHolidayRequest $request, Branch $branch, BranchHoliday $holiday, UpdateBranchHoliday $action): BranchHolidayResource
    {
        $this->authorize('update', $branch);

        if ($holiday->branch_id !== $branch->id) {
            return response()->json(['error' => 'Holiday not found'], 404);
        }

        $holiday = $action->execute($holiday, $request->validated(), $request->user());

        return new BranchHolidayResource($holiday);
    }

    public function destroy(Request $request, Branch $branch, BranchHoliday $holiday, DeleteBranchHoliday $action): Response
    {
        $this->authorize('update', $branch);

        if ($holiday->branch_id !== $branch->id) {
            return response()->json(['error' => 'Holiday not found'], 404);
        }

        $action->execute($holiday, $request->user());

        return response()->noContent();
    }
}
