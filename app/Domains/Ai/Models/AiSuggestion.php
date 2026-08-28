<?php

namespace App\Domains\Ai\Models;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiSuggestion extends Model
{
    protected $fillable = [
        'ticket_id',
        'chat_session_id',
        'feature',
        'state',
        'content',
        'confidence',
        'model',
        'requested_by_user_id',
        'resolved_by_user_id',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'feature' => AiFeature::class,
        'state' => AiSuggestionState::class,
        'confidence' => 'float',
        'resolved_at' => 'datetime',
        'metadata' => 'json',
    ];

    protected $hidden = ['id'];

    /**
     * Boot the model with UUID generation.
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid ??= Str::uuid();
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
