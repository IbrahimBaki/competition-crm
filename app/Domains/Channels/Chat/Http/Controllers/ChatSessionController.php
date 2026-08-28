<?php

namespace App\Domains\Channels\Chat\Http\Controllers;

use App\Domains\Channels\Chat\Actions\AcceptChatSession;
use App\Domains\Channels\Chat\Actions\EndChatSession;
use App\Domains\Channels\Chat\Actions\PostChatMessage;
use App\Domains\Channels\Chat\Actions\TransferChatSession;
use App\Domains\Channels\Chat\Http\Requests\EndChatSessionRequest;
use App\Domains\Channels\Chat\Http\Requests\StoreChatMessageRequest;
use App\Domains\Channels\Chat\Http\Requests\TransferChatSessionRequest;
use App\Domains\Channels\Chat\Http\Resources\ChatMessageResource;
use App\Domains\Channels\Chat\Http\Resources\ChatSessionResource;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatTransferTargetType;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ChatSessionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Queue + active session list for the console. Scoped to the agent's
     * departments unless they hold channels.chat.manage (see ChatSessionPolicy).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ChatSession::class);

        $user = $request->user();

        $query = ChatSession::query()->with(['visitor', 'assignee', 'department', 'branch']);

        if (! $user->can(PermissionKey::CHANNELS_CHAT_MANAGE)) {
            $query->whereIn('department_id', $user->departments()->pluck('departments.id'));
        }

        if ($state = $request->query('state')) {
            $query->whereIn('state', explode(',', $state));
        } else {
            // Default view: only sessions still relevant to the console.
            $query->whereIn('state', ['queued', 'active', 'transferred']);
        }

        $sessions = $query->orderBy('queued_at')->paginate((int) $request->query('per_page', 25));

        return ApiResponse::collection($sessions->through(fn (ChatSession $s) => new ChatSessionResource($s)));
    }

    public function messages(Request $request, ChatSession $session)
    {
        $this->authorize('view', $session);

        $messages = $session->messages()->paginate((int) $request->query('per_page', 50));

        return ApiResponse::collection($messages->through(fn ($m) => new ChatMessageResource($m)));
    }

    public function storeMessage(StoreChatMessageRequest $request, ChatSession $session, PostChatMessage $action)
    {
        $this->authorize('sendMessage', $session);

        // sendMessage() only passes for the assigned agent (or a manage-level
        // holder), so the session has already been through accept() and has a
        // ticket — no assignment bookkeeping needed here.
        $message = $action->handle(
            $session,
            'agent',
            $request->validated('body'),
            $request->validated('client_message_id'),
        );

        return ApiResponse::created(new ChatMessageResource($message));
    }

    public function accept(Request $request, ChatSession $session, AcceptChatSession $action)
    {
        $this->authorize('accept', $session);

        /** @var User $agent */
        $agent = $request->user();
        $session = $action->handle($session, $agent);

        return ApiResponse::ok(new ChatSessionResource($session));
    }

    public function transfer(TransferChatSessionRequest $request, ChatSession $session, TransferChatSession $action)
    {
        $this->authorize('transfer', $session);

        $targetType = ChatTransferTargetType::from($request->validated('target_type'));
        $targetAgent = $request->validated('target_agent_uuid')
            ? User::where('uuid', $request->validated('target_agent_uuid'))->first()
            : null;

        $session = $action->handle($session, $targetType, $request->validated('reason'), $targetAgent);

        return ApiResponse::ok(new ChatSessionResource($session));
    }

    public function end(EndChatSessionRequest $request, ChatSession $session, EndChatSession $action)
    {
        $this->authorize('end', $session);

        $session = $action->handle($session, $request->validated('reason'));

        return ApiResponse::ok(new ChatSessionResource($session));
    }
}
