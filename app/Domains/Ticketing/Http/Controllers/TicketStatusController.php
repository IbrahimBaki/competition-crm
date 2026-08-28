<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Http\Requests\StoreTicketStatusRequest;
use App\Domains\Ticketing\Http\Requests\UpdateTicketStatusRequest;
use App\Domains\Ticketing\Http\Resources\TicketStatusResource;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class TicketStatusController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', TicketStatusDefinition::class);

        $statuses = TicketStatusDefinition::orderBy('position')->paginate();

        return ApiResponse::collection($statuses);
    }

    public function store(StoreTicketStatusRequest $request)
    {
        $this->authorize('create', TicketStatusDefinition::class);

        $status = TicketStatusDefinition::create([
            'key' => Str::slug($request->input('name.en')),
            'name' => $request->input('name'),
            'lifecycle_type' => $request->input('lifecycle_type'),
            'position' => $request->input('position', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return ApiResponse::created(new TicketStatusResource($status));
    }

    public function update(UpdateTicketStatusRequest $request, TicketStatusDefinition $status)
    {
        $this->authorize('update', $status);

        $data = [];

        if ($request->has('name')) {
            $data['name'] = $request->input('name');
        }

        if ($request->has('position')) {
            $data['position'] = $request->input('position');
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->has('is_default') && $request->boolean('is_default')) {
            TicketStatusDefinition::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $status->update($data);

        return ApiResponse::ok(new TicketStatusResource($status));
    }

    public function destroy(TicketStatusDefinition $status)
    {
        $this->authorize('delete', $status);

        $status->delete();

        return ApiResponse::noContent();
    }
}
