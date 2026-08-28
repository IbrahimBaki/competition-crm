<?php

namespace App\Domains\Channels\Chat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatSessionEvent extends Model
{
    protected $fillable = [
        'chat_session_id',
        'type',
        'actor_user_id',
        'reason',
        'meta',
    ];

    protected $casts = [
        'type' => ChatSessionEventType::class,
        'meta' => 'array',
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

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'uuid');
    }
}
