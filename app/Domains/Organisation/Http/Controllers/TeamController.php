<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\ActivateTeam;
use App\Domains\Organisation\Actions\CreateTeam;
use App\Domains\Organisation\Actions\DeactivateTeam;
use App\Domains\Organisation\Actions\UpdateTeam;
use App\Domains\Organisation\Http\Requests\StoreTeamRequest;
use App\Domains\Organisation\Http\Requests\UpdateTeamRequest;
use App\Domains\Organisation\Http\Resources\TeamResource;
use App\Domains\Organisation\Models\Team;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['id', 'name', 'created_at', '-id', '-name', '-created_at'])
            ->withFilters(['is_active' => ['eq', 'neq']])
            ->withSearchableColumns(['name']);

        $query = new CollectionQuery($request, $spec);
        $data = $query->paginate(Team::query());

        return ApiResponse::collection(
            $data,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);

        return ApiResponse::item(new TeamResource($team));
    }

    public function store(StoreTeamRequest $request, CreateTeam $action)
    {
        $this->authorize('create', Team::class);
        $team = $action->execute($request->validated(), $request->user());

        return ApiResponse::item(new TeamResource($team), 201);
    }

    public function update(UpdateTeamRequest $request, Team $team, UpdateTeam $action)
    {
        $this->authorize('update', $team);
        $team = $action->execute($team, $request->validated(), $request->user());

        return ApiResponse::item(new TeamResource($team));
    }

    public function activate(Team $team, ActivateTeam $action)
    {
        $this->authorize('update', $team);
        $team = $action->execute($team, auth()->user());

        return ApiResponse::item(new TeamResource($team));
    }

    public function deactivate(Team $team, DeactivateTeam $action)
    {
        $this->authorize('delete', $team);
        $team = $action->execute($team, auth()->user());

        return ApiResponse::item(new TeamResource($team));
    }
}
