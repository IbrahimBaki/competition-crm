<?php

namespace Database\Factories;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Models\ChatVisitorIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatSessionFactory extends Factory
{
    protected $model = ChatSession::class;

    public function definition(): array
    {
        return [
            'chat_visitor_identity_id' => ChatVisitorIdentity::factory(),
            'state' => ChatSessionState::Requested->value,
            'requested_at' => now(),
        ];
    }
}
