<?php

namespace App\Domains\Automation\Models;

use App\Domains\Organisation\Models\Department;
use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AutomationRule extends Model
{
    protected $table = 'automation_rules';

    protected $fillable = [
        'uuid',
        'key',
        'name',
        'trigger',
        'department_id',
        'priority',
        'stop_on_match',
        'is_active',
        'conditions',
        'actions',
        'escalation_level',
        'cooldown_minutes',
    ];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'trigger' => RuleTrigger::class,
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'bool',
        'stop_on_match' => 'bool',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(AutomationRuleExecution::class);
    }
}
