<?php

namespace App\Domains\Workspace\Http\Controllers;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Workspace\Actions\ChangeAgentTaskState;
use App\Domains\Workspace\Actions\CreateAgentTask;
use App\Domains\Workspace\Actions\UpdateAgentTask;
use App\Domains\Workspace\Http\Requests\StoreAgentTaskRequest;
use App\Domains\Workspace\Http\Requests\UpdateAgentTaskRequest;
use App\Domains\Workspace\Http\Resources\AgentTaskResource;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskState;
use App\Models\User;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

readonly class AgentTaskController
{
    use AuthorizesRequests;

    public function __construct(
        private CreateAgentTask $createTask,
        private ChangeAgentTaskState $changeState,
        private UpdateAgentTask $updateTask,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AgentTask::class);

        $request = request();

        // owner_id/ticket_id arrive as UUIDs (API contract); resolve to internal IDs
        // before CollectionQuery filters agent_tasks' numeric foreign key columns.
        if ($request->filled('filter.owner_id.eq')) {
            $ownerId = User::where('uuid', $request->input('filter.owner_id.eq'))->value('id');
            $request->merge(['filter' => array_replace($request->input('filter', []), [
                'owner_id' => ['eq' => $ownerId],
            ])]);
        }

        if ($request->filled('filter.ticket_id.eq')) {
            $ticketId = Ticket::where('uuid', $request->input('filter.ticket_id.eq'))->value('id');
            $request->merge(['filter' => array_replace($request->input('filter', []), [
                'ticket_id' => ['eq' => $ticketId],
            ])]);
        }

        $spec = (new CollectionQuerySpec)
            ->withSorts(['due_at', 'created_at'])
            ->withFilters([
                'state' => ['eq'],
                'owner_id' => ['eq'],
                'ticket_id' => ['eq'],
                'due_before' => ['lte'],
                'due_after' => ['gte'],
            ])
            ->withSearchableColumns([]);

        $query = AgentTask::query()->with('owner');

        // Handle special overdue filter
        if ($request->has('filter.overdue')) {
            $query->overdue();
        }

        $collectionQuery = new CollectionQuery($request, $spec);

        // Apply standard filters and pagination
        $paginated = $collectionQuery->paginate($query);

        $meta = $collectionQuery->meta();

        return ApiResponse::paginated(
            AgentTaskResource::collection($paginated),
            $paginated,
            $meta,
        )->toResponse($request);
    }

    public function store(StoreAgentTaskRequest $request): JsonResponse
    {
        $this->authorize('create', AgentTask::class);

        // Resolve UUID to ID for owner
        $owner = User::where('uuid', $request->input('owner_id'))->firstOrFail();

        // Resolve UUIDs to IDs for ticket and branch (if provided)
        $ticketId = null;
        if ($request->filled('ticket_id')) {
            $ticket = Ticket::where('uuid', $request->input('ticket_id'))->first();
            $ticketId = $ticket?->id;
        }

        $branchId = null;
        if ($request->filled('branch_id')) {
            // Branch uses 'id' as the UUID column, so we can get it directly
            $branchId = $request->input('branch_id');
        }

        $task = $this->createTask->handle(
            owner: $owner,
            creator: $request->user(),
            title: $request->input('title'),
            description: $request->input('description'),
            dueAt: $request->input('due_at') ? now()->parse($request->input('due_at')) : null,
            dueInWorkingTime: (bool) $request->input('due_in_working_time', false),
            ticketId: $ticketId,
            branchId: $branchId,
        );

        return ApiResponse::item(new AgentTaskResource($task), 201)
            ->toResponse(request());
    }

    public function show(AgentTask $task): JsonResponse
    {
        $this->authorize('view', $task);

        return ApiResponse::item(new AgentTaskResource($task))
            ->toResponse(request());
    }

    public function update(UpdateAgentTaskRequest $request, AgentTask $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->updateTask->handle($task, $request->validated(), $request->user());

        return ApiResponse::item(new AgentTaskResource($task))
            ->toResponse(request());
    }

    public function destroy(AgentTask $task): JsonResponse
    {
        $this->authorize('update', $task);

        // Route through ChangeAgentTaskState to produce an audit event
        $this->changeState->handle($task, AgentTaskState::Cancelled, auth()->user());

        return response()->noContent();
    }
}
