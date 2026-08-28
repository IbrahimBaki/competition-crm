<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    protected $table = 'report_schedules';

    protected $fillable = [
        'uuid',
        'name',
        'report_key',
        'format',
        'filters',
        'frequency',
        'day_of_week',
        'day_of_month',
        'run_at_time',
        'timezone',
        'recipients',
        'is_active',
        'created_by_user_id',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'immutable_datetime',
        'next_run_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
