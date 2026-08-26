<?php

namespace App\Domains\Automation\Models;

use App\Domains\Ticketing\Exceptions\AutomationRuleExecutionImmutableException;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AutomationRuleExecution extends Model
{
    protected $table = 'automation_rule_executions';

    protected $fillable = [
        'uuid',
        'automation_rule_id',
        'ticket_id',
        'actor_id',
        'trigger',
        'outcome',
        'condition_snapshot',
        'changes',
        'reason',
        'idempotency_key',
        'executed_at',
    ];

    protected $casts = [
        'condition_snapshot' => 'array',
        'changes' => 'array',
        'outcome' => RuleExecutionOutcome::class,
        'executed_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->uuid) {
                $model->uuid = Str::uuid();
            }
        });

        static::updating(function () {
            throw new AutomationRuleExecutionImmutableException('Automation rule executions are immutable');
        });

        static::deleting(function () {
            throw new AutomationRuleExecutionImmutableException('Automation rule executions cannot be deleted');
        });
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
