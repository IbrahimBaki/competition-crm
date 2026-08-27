<?php

namespace App\Domains\Ai\Models;

use App\Domains\Channels\Chat\Models\ChatMessage;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotTurn extends Model
{
    protected $fillable = [
        'chat_session_id',
        'visitor_message_id',
        'bot_message_id',
        'answered',
        'grounded_article_id',
        'confidence',
        'failure_streak',
        'handoff_triggered',
    ];

    protected $casts = [
        'answered' => 'boolean',
        'confidence' => 'float',
        'handoff_triggered' => 'boolean',
    ];

    protected $hidden = ['id'];

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function visitorMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'visitor_message_id');
    }

    public function botMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'bot_message_id');
    }

    public function groundedArticle(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'grounded_article_id');
    }
}
