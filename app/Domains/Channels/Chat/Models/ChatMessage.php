<?php

namespace App\Domains\Channels\Chat\Models;

use App\Domains\Ticketing\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'sequence',
        'author_type',
        'author_user_id',
        'body',
        'sent_at',
        'client_message_id',
    ];

    protected $casts = [
        'author_type' => ChatParticipantType::class,
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->uuid ??= Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id', 'uuid');
    }

    public function ticketMessage(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class);
    }
}
