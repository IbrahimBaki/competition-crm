<?php

namespace App\Domains\Channels\Chat\Models;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    protected $fillable = [
        'uuid',
        'chat_visitor_identity_id',
        'ticket_id',
        'branch_id',
        'department_id',
        'assigned_user_id',
        'state',
        'handled_by',
        'subject',
        'initial_message',
        'queue_position',
        'end_reason',
        'requested_at',
        'queued_at',
        'activated_at',
        'transferred_at',
        'ended_at',
        'abandoned_at',
        'transcript_persisted_at',
        'last_visitor_seen_at',
        'last_agent_seen_at',
        'disconnected_at',
    ];

    protected $casts = [
        'state' => ChatSessionState::class,
        'requested_at' => 'datetime',
        'queued_at' => 'datetime',
        'activated_at' => 'datetime',
        'transferred_at' => 'datetime',
        'ended_at' => 'datetime',
        'abandoned_at' => 'datetime',
        'transcript_persisted_at' => 'datetime',
        'last_visitor_seen_at' => 'datetime',
        'last_agent_seen_at' => 'datetime',
        'disconnected_at' => 'datetime',
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

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(ChatVisitorIdentity::class, 'chat_visitor_identity_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'uuid');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('sequence');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ChatSessionEvent::class);
    }
}
