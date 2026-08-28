<?php

namespace App\Domains\Workspace\Models;

use App\Models\User;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTaskEvent extends Model
{
    use GeneratesUuid;

    protected $guarded = ['*'];

    protected $fillable = ['agent_task_id', 'actor_id', 'type', 'payload'];

    protected $casts = [
        'type' => AgentTaskEventType::class,
        'payload' => 'array',
    ];

    public const UPDATED_AT = null;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function agentTask(): BelongsTo
    {
        return $this->belongsTo(AgentTask::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
