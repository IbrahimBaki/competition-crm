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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);
        $teams = Team::paginate($request->input('per_page', 25));

        return response()->json([
            'data' => TeamResource::collection($teams),
            'meta' => [
                'page' => $teams->currentPage(),
                'per_page' => $teams->perPage(),
                'total' => $teams->total(),
            ],
            'links' => [
                'first' => $teams->url(1),
                'last' => $teams->url($teams->lastPage()),
                'prev' => $teams->previousPageUrl(),
                'next' => $teams->nextPageUrl(),
            ],
        ]);
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);

        return response()->json(['data' => new TeamResource($team)]);
    }

    public function store(StoreTeamRequest $request, CreateTeam $action)
    {
        $this->authorize('create', Team::class);
        $team = $action->execute($request->validated(), $request->user());

        return response()->json(['data' => new TeamResource($team)], 201);
    }

    public function update(UpdateTeamRequest $request, Team $team, UpdateTeam $action)
    {
        $this->authorize('update', $team);
        $team = $action->execute($team, $request->validated(), $request->user());

        return response()->json(['data' => new TeamResource($team)]);
    }

    public function activate(Team $team, ActivateTeam $action)
    {
        $this->authorize('update', $team);
        $team = $action->execute($team, auth()->user());

        return response()->json(['data' => new TeamResource($team)]);
    }

    public function deactivate(Team $team, DeactivateTeam $action)
    {
        $this->authorize('delete', $team);
        $team = $action->execute($team, auth()->user());

        return response()->json(['data' => new TeamResource($team)]);
    }
}
