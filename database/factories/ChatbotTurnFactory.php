<?php

namespace Database\Factories;

use App\Domains\Ai\Models\ChatbotTurn;
use App\Domains\Channels\Chat\Models\ChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatbotTurnFactory extends Factory
{
    protected $model = ChatbotTurn::class;

    public function definition(): array
    {
        return [
            'chat_session_id' => ChatSession::factory(),
            'visitor_message_id' => null,
            'bot_message_id' => null,
            'answered' => true,
            'grounded_article_id' => null,
            'confidence' => 0.8,
            'failure_streak' => 0,
            'handoff_triggered' => false,
        ];
    }
}
