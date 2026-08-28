<?php

namespace App\Domains\Sla\Http\Controllers;

use App\Domains\Sla\Http\Requests\StoreSlaPolicyRequest;
use App\Domains\Sla\Http\Requests\UpdateSlaPolicyRequest;
use App\Domains\Sla\Http\Resources\SlaPolicyResource;
use App\Domains\Sla\Models\SlaPolicy;
use App\Support\Http\ApiResponse;
use Illuminate\Routing\Controller;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', SlaPolicy::class);

        $policies = SlaPolicy::paginate();

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
}
