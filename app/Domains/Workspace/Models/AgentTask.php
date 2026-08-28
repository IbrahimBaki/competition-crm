<?php

namespace App\Domains\Workspace\Models;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentTask extends Model
{
    use GeneratesUuid;
    use HasFactory;

    protected $guarded = ['*'];

    protected $fillable = [
        'owner_id',
        'created_by_id',
        'ticket_id',
        'title',
        'description',
        'due_at',
        'due_in_working_time',
        'branch_id',
        'state',
        'completed_at',
        'cancelled_at',
        'reminder_at',
        'reminder_sent_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reminder_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'state' => AgentTaskState::class,
        'due_in_working_time' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AgentTaskEvent::class, 'agent_task_id')->orderBy('created_at');
    }

    public function scopeOverdue($query)
    {
        return $query
            ->whereIn('state', [AgentTaskState::Open->value, AgentTaskState::InProgress->value])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }
}
