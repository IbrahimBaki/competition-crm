<?php

namespace App\Domains\Channels\Chat\Http\Controllers;

use App\Domains\Channels\Chat\Actions\EndChatSession;
use App\Domains\Channels\Chat\Actions\PostChatMessage;
use App\Domains\Channels\Chat\Actions\RequestChatSession;
use App\Domains\Channels\Chat\Http\Requests\PublicChatMessageRequest;
use App\Domains\Channels\Chat\Http\Requests\PublicEndChatSessionRequest;
use App\Domains\Channels\Chat\Http\Resources\ChatMessageResource;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Organisation\Models\Department;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicChatSessionController
{
    public function __construct(
        private readonly RequestChatSession $requestSession,
    ) {}

    public function departments(Request $request): JsonResponse
    {
        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Department $department) => [
                'id' => $department->getKey(),
                'name' => $department->name,
            ]);

        return response()->json(['data' => $departments->values()]);
    }

    public function store(Request $request): JsonResponse
    {
        $dept = Department::findOrFail($request->input('department'));
        $result = $this->requestSession->handle(
            department: $dept,
            visitorToken: $request->input('visitor_token'),
            displayName: $request->input('display_name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            locale: $request->input('locale', 'en'),
            userAgent: $request->userAgent(),
        );

        // was ->toResponse($request, 201): toResponse() takes no status
        // argument, so the 201 was silently dropped and this returned 200.
        return ApiResponse::created([
            'session_uuid' => $result->session->uuid,
            'visitor_token' => $result->visitorToken,
            'availability' => $result->availability->outcome->value,
            'queue_position' => $result->availability->queuePosition,
        ])->toResponse($request);
    }

    public function messages(Request $request, ChatSession $session): JsonResponse
    {
        $this->assertVisitor($session, $request->query('visitor_token'));

        $messages = $session->messages()->paginate((int) $request->query('per_page', 50));

        return ApiResponse::collection($messages->through(fn ($m) => new ChatMessageResource($m)))
            ->toResponse($request);
    }

    public function storeMessage(PublicChatMessageRequest $request, ChatSession $session, PostChatMessage $action): JsonResponse
    {
        $this->assertVisitor($session, $request->validated('visitor_token'));

        $message = $action->handle(
            $session,
            'visitor',
            $request->validated('body'),
            $request->validated('client_message_id'),
        );

        return ApiResponse::created(new ChatMessageResource($message))->toResponse($request);
    }

    public function end(PublicEndChatSessionRequest $request, ChatSession $session, EndChatSession $action): JsonResponse
    {
        $this->assertVisitor($session, $request->validated('visitor_token'));

        $session = $action->handle($session, $request->validated('reason'));

        return ApiResponse::item(['state' => $session->state->value])->toResponse($request);
    }

    /**
     * A visitor may only reach their own session. Unauthorized access returns
     * 404 (not 403) so the token's validity is never disclosed — the same
     * rule attachments and guest ticket tracking already follow.
     */
    private function assertVisitor(ChatSession $session, ?string $visitorToken): void
    {
        if (! $visitorToken || $session->visitor?->visitor_token !== $visitorToken) {
            throw new NotFoundHttpException;
        }
    }
}
