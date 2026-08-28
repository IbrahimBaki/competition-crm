<?php

namespace App\Domains\Sla\Http\Controllers;

use App\Domains\Sla\Exceptions\SlaPolicyInUseException;
use App\Domains\Sla\Http\Requests\StoreSlaPolicyRequest;
use App\Domains\Sla\Http\Requests\UpdateSlaPolicyRequest;
use App\Domains\Sla\Http\Resources\SlaPolicyResource;
use App\Domains\Sla\Models\SlaPolicy;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class SlaPolicyController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', SlaPolicy::class);

        // Wrap through the Resource: SlaPolicy has a bigint `id` alongside its
        // `uuid`, so returning raw models here would expose the internal id and
        // break the UUID-only contract that show/store/update already honour.
        $policies = SlaPolicy::paginate()
            ->through(fn (SlaPolicy $policy) => new SlaPolicyResource($policy));

        return ApiResponse::collection($policies);
    }

    public function store(StoreSlaPolicyRequest $request)
    {
        $this->authorize('create', SlaPolicy::class);

        $policy = SlaPolicy::create($request->validated());

        return ApiResponse::created(new SlaPolicyResource($policy));
    }

    public function show(SlaPolicy $policy)
    {
        $this->authorize('view', $policy);

        return ApiResponse::ok(new SlaPolicyResource($policy));
    }

    public function update(UpdateSlaPolicyRequest $request, SlaPolicy $policy)
    {
        $this->authorize('update', $policy);

        $policy->update($request->validated());

        return ApiResponse::ok(new SlaPolicyResource($policy));
    }

    public function destroy(SlaPolicy $policy)
    {
        $this->authorize('delete', $policy);

        // apiResource registers DELETE, so this must exist; a policy that is
        // still driving clocks (or is the branch default) cannot be removed
        // without orphaning SLA state.
        if ($policy->is_default || $policy->clocks()->exists()) {
            throw new SlaPolicyInUseException;
        }

        $policy->delete();

        return ApiResponse::noContent();
    }
}
