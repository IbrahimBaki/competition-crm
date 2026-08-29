<?php

namespace App\Domains\Workspace\Http\Controllers;

use App\Domains\Workspace\Actions\CreateQuickReply;
use App\Domains\Workspace\Actions\DeleteQuickReply;
use App\Domains\Workspace\Actions\UpdateQuickReply;
use App\Domains\Workspace\Http\Requests\StoreQuickReplyRequest;
use App\Domains\Workspace\Http\Requests\UpdateQuickReplyRequest;
use App\Domains\Workspace\Http\Resources\QuickReplyResource;
use App\Domains\Workspace\Models\QuickReply;
use App\Domains\Workspace\Models\QuickReplyScope;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

readonly class QuickReplyController
{
    use AuthorizesRequests;

    public function __construct(
        private CreateQuickReply $createAction,
        private UpdateQuickReply $updateAction,
        private DeleteQuickReply $deleteAction,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', QuickReply::class);

        $request = request();

        $spec = (new CollectionQuerySpec)
            ->withSorts(['created_at'])
            ->withFilters([
                'scope' => ['eq'],
            ])
            ->withSearchableColumns(['title']);

        $query = QuickReply::where(function ($q) {
            $q->where('owner_id', auth()->id())->orWhereNull('owner_id');
        });

        $collectionQuery = new CollectionQuery($request, $spec);
        $paginated = $collectionQuery->paginate($query);

        $meta = $collectionQuery->meta();

        return ApiResponse::paginated(
            QuickReplyResource::collection($paginated),
            $paginated,
            $meta,
        )->toResponse($request);
    }

    public function store(StoreQuickReplyRequest $request): JsonResponse
    {
        $reply = $this->createAction->handle(
            scope: QuickReplyScope::from($request->input('scope')),
            owner: auth()->user(),
            title: $request->input('title'),
            body: $request->input('body'),
            creator: auth()->user(),
        );

        return ApiResponse::item(new QuickReplyResource($reply), 201)
            ->toResponse(request());
    }

    public function show(QuickReply $reply): JsonResponse
    {
        $this->authorize('view', $reply);

        return ApiResponse::item(new QuickReplyResource($reply))
            ->toResponse(request());
    }

    public function update(UpdateQuickReplyRequest $request, QuickReply $reply): JsonResponse
    {
        $this->authorize('update', $reply);

        $updated = $this->updateAction->handle($reply, $request->validated());

        return ApiResponse::item(new QuickReplyResource($updated))
            ->toResponse(request());
    }

    public function destroy(QuickReply $reply): JsonResponse
    {
        $this->authorize('delete', $reply);

        $this->deleteAction->handle($reply);

        return response()->noContent();
    }
}
