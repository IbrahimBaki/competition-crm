<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\ReplaceBranchWorkingHours;
use App\Domains\Organisation\Http\Requests\ReplaceBranchWorkingHoursRequest;
use App\Domains\Organisation\Http\Resources\BranchWorkingHourResource;
use App\Domains\Organisation\Models\Branch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class BranchWorkingHourController extends Controller
{
    use AuthorizesRequests;

    public function index(Branch $branch): AnonymousResourceCollection
    {
        $this->authorize('view', $branch);

        return BranchWorkingHourResource::collection($branch->workingHours);
    }

    public function update(ReplaceBranchWorkingHoursRequest $request, Branch $branch, ReplaceBranchWorkingHours $action): AnonymousResourceCollection
    {
        $this->authorize('update', $branch);

        $action->execute($branch, $request->input('days'), $request->user());

        return BranchWorkingHourResource::collection($branch->fresh()->workingHours);
    }
}
