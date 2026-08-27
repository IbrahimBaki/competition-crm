<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Services\Availability\ChatAvailabilityResolver;
use App\Domains\Channels\Chat\Services\Identity\ResolveChatVisitor;
use App\Domains\Organisation\Models\Department;
use Illuminate\Support\Facades\DB;

final class RequestChatSession
{
    public function __construct(
        private readonly ResolveChatVisitor $resolveVisitor,
        private readonly ChatAvailabilityResolver $availabilityResolver,
    ) {}

    public function handle(
        Department $department,
        ?string $visitorToken,
        ?string $displayName,
        ?string $email,
        ?string $phone,
        ?string $locale,
        ?string $userAgent,
        ?string $initialMessage = null,
    ): ChatSessionResult {
        return DB::transaction(function () use (
            $department,
            $visitorToken,
            $displayName,
            $email,
            $phone,
            $locale,
            $userAgent,
            $initialMessage,
        ) {
            $visitor = $this->resolveVisitor->handle($visitorToken, $displayName, $email, $phone, $locale, $userAgent);
            $availability = $this->availabilityResolver->resolve($department);

            $session = ChatSession::create([
                'chat_visitor_identity_id' => $visitor->id,
                'branch_id' => $department->branch_id,
                'department_id' => $department->id,
                'state' => ChatSessionState::Requested->value,
                'requested_at' => now(),
            ]);

            if ($initialMessage) {
                $session->messages()->create([
                    'sequence' => 1,
                    'author_type' => 'visitor',
                    'body' => $initialMessage,
                    'sent_at' => now(),
                ]);
            }

            return new ChatSessionResult($session, $visitor->visitor_token, $availability);
        });
    }
}
