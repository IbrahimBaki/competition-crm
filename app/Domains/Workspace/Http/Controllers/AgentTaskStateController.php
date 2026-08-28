<?php

namespace App\Domains\Workspace\Http\Controllers;

use App\Domains\Workspace\Actions\ChangeAgentTaskState;
use App\Domains\Workspace\Http\Requests\ChangeAgentTaskStateRequest;
use App\Domains\Workspace\Http\Resources\AgentTaskResource;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskState;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

readonly class AgentTaskStateController
{
    use AuthorizesRequests;

    public function __construct(private ChangeAgentTaskState $changeState) {}

    public function store(ChangeAgentTaskStateRequest $request, AgentTask $task): JsonResponse
    {
        $this->authorize('changeState', $task);

        $updated = $this->changeState->handle(
            $task,
            AgentTaskState::from($request->input('state')),
            auth()->user()
        );

        return ApiResponse::item(new AgentTaskResource($updated))
            ->toResponse(request());
    }

    public function update(ChangeAgentTaskStateRequest $request, AgentTask $task): JsonResponse
    {
        $this->authorize('changeState', $task);

        $updated = $this->changeState->handle(
            $task,
            AgentTaskState::from($request->input('state')),
            auth()->user()
        );

        return ApiResponse::item(new AgentTaskResource($updated))
            ->toResponse(request());
    }
}
